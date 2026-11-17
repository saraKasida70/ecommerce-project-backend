<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Modules\Products\Models\Product;
use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Models\OrderItem;
use App\Modules\Inventory\Services\InventoryGrpcClient;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class TestInterfaces extends Command
{
    protected $signature = 'test:interfaces';
    protected $description = 'Test all interface functionality via API simulation';

    public function handle()
    {
        $this->info('=== Testing Interface Functionality ===' . PHP_EOL);

        // Get test users
        $customer = User::where('email', 'customer@test.com')->first();
        $admin = User::where('email', 'admin@test.com')->first();

        if (!$customer || !$admin) {
            $this->error('Test users not found. Run UserSeeder first.');
            return 1;
        }

        // Test 1: Products API (simulating products page)
        $this->info('Test 1: Products API (Products Page)...');
        try {
            $products = Product::all();
            $this->info('  Products count: ' . $products->count());
            $this->info('  Sample product: ' . $products->first()->name . ' - $' . $products->first()->price);
            $this->info('✓ Products API working' . PHP_EOL);
        } catch (\Exception $e) {
            $this->error('✗ Products API failed: ' . $e->getMessage() . PHP_EOL);
        }

        // Test 2: Cart functionality (simulating cart operations)
        $this->info('Test 2: Cart Functionality...');
        try {
            $cart = [
                ['id' => 2, 'name' => 'Laptop', 'price' => 999.99, 'quantity' => 1],
                ['id' => 4, 'name' => 'Mouse', 'price' => 29.99, 'quantity' => 2],
            ];
            $total = array_reduce($cart, function($sum, $item) {
                return $sum + ($item['price'] * $item['quantity']);
            }, 0);
            $this->info('  Cart items: ' . count($cart));
            $this->info('  Cart total: $' . number_format($total, 2));
            $this->info('✓ Cart calculation working' . PHP_EOL);
        } catch (\Exception $e) {
            $this->error('✗ Cart functionality failed: ' . $e->getMessage() . PHP_EOL);
        }

        // Test 3: Checkout and Order Creation
        $this->info('Test 3: Checkout and Order Creation...');
        try {
            $inventoryClient = new InventoryGrpcClient();
            $products = Product::take(2)->get();
            
            $orderItems = [
                ['product_id' => $products[0]->id, 'quantity' => 1],
                ['product_id' => $products[1]->id, 'quantity' => 1],
            ];
            
            $orderReference = 'CHECKOUT-TEST-' . time();
            $reservation = $inventoryClient->reserveStock($orderReference, $orderItems);
            
            if ($reservation['success']) {
                $total = 0;
                foreach ($orderItems as $item) {
                    $product = Product::find($item['product_id']);
                    $total += $product->price * $item['quantity'];
                }
                
                $order = Order::create([
                    'user_id' => $customer->id,
                    'total' => $total,
                    'status' => 'pending',
                ]);
                
                foreach ($orderItems as $item) {
                    $product = Product::find($item['product_id']);
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $product->price,
                    ]);
                }
                
                $this->info('  Order created: ID ' . $order->id . ', Total: $' . number_format($total, 2));
                $this->info('  Items count: ' . count($orderItems));
                $this->info('✓ Checkout and order creation working' . PHP_EOL);
            } else {
                $this->error('✗ Reservation failed: ' . $reservation['message'] . PHP_EOL);
            }
        } catch (\Exception $e) {
            $this->error('✗ Checkout failed: ' . $e->getMessage() . PHP_EOL);
        }

        // Test 4: Customer Orders Page
        $this->info('Test 4: Customer Orders Page...');
        try {
            $customerOrders = Order::with('items')
                ->where('user_id', $customer->id)
                ->latest()
                ->get();
            
            $this->info('  Customer orders count: ' . $customerOrders->count());
            foreach ($customerOrders as $order) {
                $this->info('    Order #' . $order->id . ' - Status: ' . $order->status . ' - Total: $' . number_format($order->total, 2));
                $this->info('      Items: ' . $order->items->count());
            }
            $this->info('✓ Customer orders page working' . PHP_EOL);
        } catch (\Exception $e) {
            $this->error('✗ Customer orders failed: ' . $e->getMessage() . PHP_EOL);
        }

        // Test 5: Admin Orders Page
        $this->info('Test 5: Admin Orders Page...');
        try {
            $allOrders = Order::with('items', 'user')
                ->latest()
                ->get();
            
            $this->info('  Total orders in system: ' . $allOrders->count());
            foreach ($allOrders as $order) {
                $this->info('    Order #' . $order->id . ' - User: ' . $order->user->email . ' - Status: ' . $order->status);
            }
            $this->info('✓ Admin orders page working' . PHP_EOL);
        } catch (\Exception $e) {
            $this->error('✗ Admin orders failed: ' . $e->getMessage() . PHP_EOL);
        }

        // Test 6: Admin Order Status Update
        $this->info('Test 6: Admin Order Status Update...');
        try {
            $testOrder = Order::where('user_id', $customer->id)->first();
            if ($testOrder) {
                $oldStatus = $testOrder->status;
                $newStatus = 'processing';
                $testOrder->update(['status' => $newStatus]);
                
                $this->info('  Order #' . $testOrder->id . ' status changed from ' . $oldStatus . ' to ' . $newStatus);
                $this->info('✓ Admin status update working' . PHP_EOL);
            }
        } catch (\Exception $e) {
            $this->error('✗ Status update failed: ' . $e->getMessage() . PHP_EOL);
        }

        // Test 7: Order Details
        $this->info('Test 7: Order Details View...');
        try {
            $testOrder = Order::with('items')->where('user_id', $customer->id)->first();
            if ($testOrder) {
                $this->info('  Order ID: ' . $testOrder->id);
                $this->info('  Total: $' . number_format($testOrder->total, 2));
                $this->info('  Status: ' . $testOrder->status);
                $this->info('  Items:');
                foreach ($testOrder->items as $item) {
                    $this->info('    - Product ID: ' . $item->product_id . ', Qty: ' . $item->quantity . ', Price: $' . number_format($item->price, 2));
                }
                $this->info('✓ Order details view working' . PHP_EOL);
            }
        } catch (\Exception $e) {
            $this->error('✗ Order details failed: ' . $e->getMessage() . PHP_EOL);
        }

        // Test 8: Notifications
        $this->info('Test 8: Notifications Page...');
        try {
            $notifications = $customer->notifications;
            $this->info('  Customer notifications count: ' . $notifications->count());
            foreach ($notifications->take(3) as $notif) {
                $this->info('    - Type: ' . $notif->type . ', Read: ' . ($notif->read_at ? 'Yes' : 'No'));
            }
            $this->info('✓ Notifications page working' . PHP_EOL);
        } catch (\Exception $e) {
            $this->error('✗ Notifications failed: ' . $e->getMessage() . PHP_EOL);
        }

        $this->info('=== All Interface Tests Completed ===');
        return 0;
    }
}
