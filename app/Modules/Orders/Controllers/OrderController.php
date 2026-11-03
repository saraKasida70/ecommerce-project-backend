<?php

namespace App\Modules\Orders\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Orders\Models\Order;
use App\Modules\Products\Services\ProductService;
use App\Modules\Notifications\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    protected ProductService $productService;

    protected NotificationService $notificationService;

    public function __construct(
        ProductService $productService,
        NotificationService $notificationService
    ) {
        $this->productService = $productService;
        $this->notificationService = $notificationService;
    }

    /**
     * عرض طلبات المستخدم الحالي.
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
     * إنشاء طلب جديد للمستخدم الحالي.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'integer',
                'distinct',
            ],
            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        $productIds = array_column($validated['items'], 'product_id');

        $order = DB::transaction(function () use (
            $request,
            $validated,
            $productIds
        ) {
            $products = $this->productService
                ->getProductsByIds($productIds)
                ->keyBy('id');

            if ($products->count() !== count($productIds)) {
                throw ValidationException::withMessages([
                    'items' => ['One or more products do not exist.'],
                ]);
            }

            $total = 0;
            $items = [];

            foreach ($validated['items'] as $item) {
                $product = $products->get($item['product_id']);

                $price = $product->price;
                $quantity = $item['quantity'];

                $total += $price * $quantity;

                $items[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price,
                ];
            }

            $order = Order::create([
                'user_id' => $request->user()->id,
                'total' => $total,
                'status' => 'pending',
            ]);

            $order->items()->createMany($items);

            return $order->load('items');
        });

        // إرسال إشعار بعد إنشاء الطلب بنجاح.
        $this->notificationService->send(
            $request->user(),
            'Order Created',
            "Your order #{$order->id} has been created successfully.",
            [
                'order_id' => $order->id,
                'status' => $order->status,
            ]
        );

        return response()->json([
            'message' => 'Order created successfully.',
            'data' => $order,
        ], 201);
    }

    /**
     * عرض تفاصيل طلب محدد.
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
     * تغيير حالة الطلب بواسطة المسؤول فقط.
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

        // إرسال إشعار إلى صاحب الطلب بعد تغيير حالته.
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
