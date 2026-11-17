<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Inventory\Services\InventoryGrpcClient;
use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Models\OrderItem;
use App\Modules\Products\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;
use Illuminate\Support\Facades\RateLimiter;
class ApiSecurityRequirementsTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp(): void
{
    parent::setUp();

    config(['cache.default' => 'array']);

    RateLimiter::clear('127.0.0.1');
}

    protected function makeUser(array $attributes = []): User
    {
        return User::factory()->create([
            'role' => 'customer',
            ...$attributes,
        ]);
    }

    protected function makeAdminUser(array $attributes = []): User
    {
        return User::factory()->create([
            'role' => 'admin',
            ...$attributes,
        ]);
    }

    protected function actingAsJwt(User $user)
    {
        $token = JWTAuth::fromUser($user);

        return $this->withHeader('Authorization', 'Bearer ' . $token);
    }

    protected function fakeInventoryClient(): InventoryGrpcClient
    {
        return new class extends InventoryGrpcClient {
            public function __construct()
            {
                // Intentionally empty for test isolation; production code still uses real gRPC client.
            }

            public function getStock(int $productId): array
            {
                return [
                    'success' => true,
                    'product_id' => $productId,
                    'available_quantity' => 10,
                    'message' => 'Stock available.',
                ];
            }

            public function addStock(int $productId, int $quantity): array
            {
                return [
                    'success' => true,
                    'message' => 'Stock added successfully.',
                    'product_id' => $productId,
                    'available_quantity' => $quantity,
                ];
            }

            public function reserveStock(string $orderReference, array $items): array
            {
                return [
                    'success' => true,
                    'message' => 'Reservation successful.',
                    'reservation_id' => 'fake-reservation-id-' . $orderReference,
                ];
            }

            public function releaseStock(string $reservationId): array
            {
                return [
                    'success' => true,
                    'message' => 'Reservation released successfully.',
                ];
            }
        };
    }

    protected function bindFakeInventoryClient(): void
    {
        app()->instance(InventoryGrpcClient::class, $this->fakeInventoryClient());
    }

    public function test_api_versioning_exists_under_v1(): void
    {
        $this->getJson('/api/v1/products')
            ->assertStatus(401);
    }

    public function test_login_returns_jwt_token_for_valid_credentials(): void
    {
        $user = $this->makeUser([
            'email' => 'alice@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'alice@example.com',
            'password' => 'password123',
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonStructure([
                'message',
                'access_token',
                'token_type',
                'expires_in',
                'user',
            ]);
    }

    public function test_access_is_rejected_without_token(): void
    {
        $this->getJson('/api/v1/products')
            ->assertStatus(401)
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_customer_can_only_see_own_orders(): void
    {
        $this->bindFakeInventoryClient();

        $customerA = $this->makeUser(['email' => 'a@example.com']);
        $customerB = $this->makeUser(['email' => 'b@example.com']);

        $orderA = Order::create([
            'user_id' => $customerA->id,
            'total' => 150.00,
            'status' => 'pending',
        ]);

        Order::create([
            'user_id' => $customerB->id,
            'total' => 300.00,
            'status' => 'pending',
        ]);

        $this->actingAsJwt($customerA)
            ->getJson('/api/v1/orders')
            ->assertStatus(200)
            ->assertJsonPath('data.0.id', $orderA->id)
            ->assertJsonCount(1, 'data');
    }

    public function test_customer_cannot_change_order_status(): void
    {
        $this->bindFakeInventoryClient();

        $customer = $this->makeUser(['email' => 'customer@example.com']);
        $order = Order::create([
            'user_id' => $customer->id,
            'total' => 120.00,
            'status' => 'pending',
        ]);

        $this->actingAsJwt($customer)
            ->patchJson('/api/v1/admin/orders/' . $order->id . '/status', [
                'status' => 'shipped',
            ])
            ->assertStatus(403);
    }

    public function test_admin_can_change_order_status(): void
    {
        $this->bindFakeInventoryClient();

        $admin = $this->makeAdminUser(['email' => 'admin@example.com']);
        $customer = $this->makeUser(['email' => 'other@example.com']);
        $order = Order::create([
            'user_id' => $customer->id,
            'total' => 200.00,
            'status' => 'pending',
        ]);

        $this->actingAsJwt($admin)
            ->patchJson('/api/v1/admin/orders/' . $order->id . '/status', [
                'status' => 'shipped',
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'shipped');
    }

    public function test_login_rate_limiting_is_enforced(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/login', [
                'email' => 'missing@example.com',
                'password' => 'wrong-password',
            ]);
        }

        $this->postJson('/api/v1/login', [
            'email' => 'missing@example.com',
            'password' => 'wrong-password',
        ])
            ->assertStatus(429);
    }

    public function test_security_headers_are_present_on_api_responses(): void
    {
        $this->getJson('/api/v1/products')
            ->assertStatus(401)
            ->assertHeader('x-content-type-options', 'nosniff')
            ->assertHeader('x-frame-options', 'DENY')
            ->assertHeader('referrer-policy', 'strict-origin-when-cross-origin')
            ->assertHeader('content-security-policy', "default-src 'self'; object-src 'none'; frame-ancestors 'none'; base-uri 'none'; form-action 'self';");
    }

    public function test_products_orders_and_notifications_routes_return_valid_payloads_for_authenticated_user(): void
    {
        $this->bindFakeInventoryClient();

        $user = $this->makeUser(['email' => 'owner@example.com']);
        $product = Product::create([
            'name' => 'Keyboard',
            'description' => 'Mechanical keyboard',
            'price' => 120.00,
            'category' => 'Electronics',
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'total' => 120.00,
            'status' => 'pending',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 120.00,
        ]);

        DB::table('notifications')->insert([
            'id' => '123e4567-e89b-12d3-a456-426614174000',
            'type' => 'App\\Notifications\\OrderCreated',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => json_encode(['order_id' => $order->id]),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAsJwt($user)
            ->getJson('/api/v1/products')
            ->assertStatus(200)
            ->assertJsonStructure(['message', 'data', 'meta']);

        $this->actingAsJwt($user)
            ->getJson('/api/v1/orders')
            ->assertStatus(200)
            ->assertJsonStructure(['message', 'data', 'meta']);

        $this->actingAsJwt($user)
            ->getJson('/api/v1/notifications')
            ->assertStatus(200)
            ->assertJsonStructure(['message', 'data', 'meta']);
    }
}
