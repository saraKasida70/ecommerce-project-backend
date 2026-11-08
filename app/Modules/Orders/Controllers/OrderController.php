<?php

namespace App\Modules\Orders\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Orders\Models\Order;
use App\Modules\Products\Services\ProductService;
use App\Modules\Notifications\Services\NotificationService;
use App\Modules\Inventory\Services\InventoryGrpcClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class OrderController extends Controller
{
    protected ProductService $productService;

    protected NotificationService $notificationService;

    protected InventoryGrpcClient $inventoryClient;

    public function __construct(
        ProductService $productService,
        NotificationService $notificationService,
        InventoryGrpcClient $inventoryClient
    ) {
        $this->productService = $productService;
        $this->notificationService = $notificationService;
        $this->inventoryClient = $inventoryClient;
    }

    /**
     * Display orders belonging to the authenticated user.
     */
    public function index(Request $request)
    {
        $orders = Order::with('items')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return response()->json([
            'message' => 'Orders retrieved successfully.',
            'data' => $orders->items(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ], 200);
    }

    /**
     * Create a new order after successfully reserving inventory.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'integer',
                'distinct',
                'min:1',
            ],
            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        $productIds = array_column(
            $validated['items'],
            'product_id'
        );

        // Retrieve products and calculate order details.
        $products = $this->productService
            ->getProductsByIds($productIds)
            ->keyBy('id');

        if ($products->count() !== count($productIds)) {
            throw ValidationException::withMessages([
                'items' => ['One or more products do not exist.'],
            ]);
        }

        $total = 0;
        $orderItems = [];

        foreach ($validated['items'] as $item) {
            $product = $products->get($item['product_id']);

            $price = $product->price;
            $quantity = $item['quantity'];

            $total += $price * $quantity;

            $orderItems[] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $price,
            ];
        }

        // Generate a unique reference for this reservation.
        $orderReference = 'ORDER-' . Str::uuid();

        // Step 1: Reserve all products through the inventory service.
        try {
            $reservation = $this->inventoryClient->reserveStock(
                $orderReference,
                $validated['items']
            );

        } catch (Throwable $e) {
            Log::error('Inventory reservation request failed', [
                'order_reference' => $orderReference,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Inventory service is currently unavailable. No order was created.',
            ], 503);
        }

        // Stop if inventory rejected the reservation.
        if (!$reservation['success']) {
            return response()->json([
                'success' => false,
                'message' => $reservation['message'],
            ], 409);
        }

        $reservationId = $reservation['reservation_id'];

        // Step 2: Save the order only after inventory approval.
        try {
            $order = DB::transaction(function () use (
                $request,
                $total,
                $orderItems
            ) {
                $order = Order::create([
                    'user_id' => $request->user()->id,
                    'total' => $total,
                    'status' => 'pending',
                ]);

                $order->items()->createMany($orderItems);

                return $order->load('items');
            });

        } catch (Throwable $e) {

            Log::error('Order creation failed after inventory reservation', [
                'reservation_id' => $reservationId,
                'order_reference' => $orderReference,
                'error' => $e->getMessage(),
            ]);

            // Step 3: Release reserved stock if saving the order fails.
            try {
                $releaseResult = $this->inventoryClient->releaseStock(
                    $reservationId
                );

                if (!$releaseResult['success']) {
                    Log::critical('Inventory reservation could not be released', [
                        'reservation_id' => $reservationId,
                        'message' => $releaseResult['message'],
                    ]);
                }

            } catch (Throwable $releaseException) {
                Log::critical('Inventory compensation failed', [
                    'reservation_id' => $reservationId,
                    'error' => $releaseException->getMessage(),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Order could not be created. The inventory reservation release was attempted.',
            ], 500);
        }

        // Step 4: Send notification after successful order creation.
        try {
            $this->notificationService->send(
                $request->user(),
                'Order Created',
                "Your order #{$order->id} has been created successfully.",
                [
                    'order_id' => $order->id,
                    'status' => $order->status,
                ]
            );

        } catch (Throwable $e) {
            Log::warning('Order notification could not be sent', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order created and inventory reserved successfully.',
            'data' => $order,
            'reservation_id' => $reservationId,
        ], 201);
    }

    /**
     * Display a specific order.
     */
    public function show(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'You are not authorized to access this order.',
                'errors' => [
                    'order' => [
                        'You cannot access another user\'s order.',
                    ],
                ],
            ], 403);
        }

        $order->load('items');

        return response()->json([
            'message' => 'Order retrieved successfully.',
            'data' => $order,
        ], 200);
    }

    /**
     * Update order status by an administrator.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => [
                'required',
                'in:pending,processing,shipped,delivered,cancelled',
            ],
        ]);

        $order->update([
            'status' => $validated['status'],
        ]);

        $this->notificationService->send(
            $order->user,
            'Order Status Updated',
            "Your order #{$order->id} status has been changed to {$order->status}.",
            [
                'order_id' => $order->id,
                'status' => $order->status,
            ]
        );

        return response()->json([
            'message' => 'Order status updated successfully.',
            'data' => [
                'id' => $order->id,
                'status' => $order->status,
            ],
        ], 200);
    }
}
