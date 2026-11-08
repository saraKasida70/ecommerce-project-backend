<?php

namespace App\Modules\Inventory\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Services\InventoryGrpcClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class InventoryController extends Controller
{
    private InventoryGrpcClient $inventoryClient;

    public function __construct(InventoryGrpcClient $inventoryClient)
    {
        $this->inventoryClient = $inventoryClient;
    }

    /**
     * Get stock for a product.
     */
    public function getStock(int $productId)
    {
        if ($productId <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Product ID must be positive.',
            ], 422);
        }

        try {
            $result = $this->inventoryClient->getStock($productId);

            return response()->json([
                'data' => $result,
            ], 200);

        } catch (Throwable $e) {
            Log::error('Get stock API failed', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Inventory service is currently unavailable.',
            ], 503);
        }
    }

    /**
     * Add stock or increase existing stock.
     */
    public function addStock(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'min:1'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $result = $this->inventoryClient->addStock(
                $validated['product_id'],
                $validated['quantity']
            );

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'product_id' => $result['product_id'],
                    'available_quantity' => $result['available_quantity'],
                ],
            ], 200);

        } catch (Throwable $e) {
            Log::error('Add stock API failed', [
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Inventory service is currently unavailable.',
            ], 503);
        }
    }

    /**
     * Reserve stock for an order.
     */
    public function reserveStock(Request $request)
    {
        $validated = $request->validate([
            'order_reference' => ['required', 'string', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'min:1'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $result = $this->inventoryClient->reserveStock(
                $validated['order_reference'],
                $validated['items']
            );

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 409);
            }

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'reservation_id' => $result['reservation_id'],
            ], 200);

        } catch (Throwable $e) {
            Log::error('Reserve stock API failed', [
                'order_reference' => $validated['order_reference'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Inventory service is currently unavailable.',
            ], 503);
        }
    }

    /**
     * Release a stock reservation.
     */
    public function releaseStock(Request $request)
    {
        $validated = $request->validate([
            'reservation_id' => ['required', 'uuid'],
        ]);

        try {
            $result = $this->inventoryClient->releaseStock(
                $validated['reservation_id']
            );

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => $result['message'],
            ], 200);

        } catch (Throwable $e) {
            Log::error('Release stock API failed', [
                'reservation_id' => $validated['reservation_id'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Inventory service is currently unavailable.',
            ], 503);
        }
    }
}
