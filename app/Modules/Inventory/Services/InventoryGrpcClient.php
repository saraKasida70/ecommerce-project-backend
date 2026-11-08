<?php

namespace App\Modules\Inventory\Services;

use Grpc\ChannelCredentials;
use Inventory\InventoryServiceClient;
use Inventory\GetStockRequest;
use Inventory\GetStockResponse;
use Inventory\ReserveStockRequest;
use Inventory\ReserveStockResponse;
use Inventory\ReleaseStockRequest;
use Inventory\ReleaseStockResponse;
use Inventory\AddStockRequest;
use Inventory\AddStockResponse;
use Inventory\StockItem;

class InventoryGrpcClient
{
    private InventoryServiceClient $client;

    public function __construct()
    {
        $host = config(
            'services.inventory.host',
            'inventory-service:50051'
        );

        $this->client = new InventoryServiceClient(
            $host,
            [
                'credentials' => ChannelCredentials::createInsecure(),
            ]
        );
    }

    /**
     * Get available stock for a product.
     */
    public function getStock(int $productId): array
    {
        $request = new GetStockRequest();
        $request->setProductId($productId);

        $result = $this->client
            ->GetStock($request, [], ['timeout' => 3000000])
            ->wait();

        /** @var GetStockResponse $response */
        $response = $result[0];
        $status = $result[1];

        if ($status->code !== 0) {
            throw new \RuntimeException(
                'Inventory service unavailable: ' . $status->details
            );
        }

        return [
            'success' => $response->getSuccess(),
            'product_id' => $response->getProductId(),
            'available_quantity' => $response->getAvailableQuantity(),
            'message' => $response->getMessage(),
        ];
    }

    /**
     * Add stock or increase existing stock.
     */
    public function addStock(int $productId, int $quantity): array
    {
        $request = new AddStockRequest();

        $request->setProductId($productId);
        $request->setQuantity($quantity);

        $result = $this->client
            ->AddStock($request, [], ['timeout' => 5000000])
            ->wait();

        /** @var AddStockResponse $response */
        $response = $result[0];
        $status = $result[1];

        if ($status->code !== 0) {
            throw new \RuntimeException(
                'Inventory service unavailable: ' . $status->details
            );
        }

        return [
            'success' => $response->getSuccess(),
            'message' => $response->getMessage(),
            'product_id' => $response->getProductId(),
            'available_quantity' => $response->getAvailableQuantity(),
        ];
    }

    /**
     * Reserve stock for an order.
     */
    public function reserveStock(
        string $orderReference,
        array $items
    ): array {
        $request = new ReserveStockRequest();
        $request->setOrderReference($orderReference);

        $stockItems = [];

        foreach ($items as $item) {
            $stockItem = new StockItem();

            $stockItem->setProductId(
                (int) $item['product_id']
            );

            $stockItem->setQuantity(
                (int) $item['quantity']
            );

            $stockItems[] = $stockItem;
        }

        $request->setItems($stockItems);

        $result = $this->client
            ->ReserveStock($request, [], ['timeout' => 5000000])
            ->wait();

        /** @var ReserveStockResponse $response */
        $response = $result[0];
        $status = $result[1];

        if ($status->code !== 0) {
            throw new \RuntimeException(
                'Inventory service unavailable: ' . $status->details
            );
        }

        return [
            'success' => $response->getSuccess(),
            'message' => $response->getMessage(),
            'reservation_id' => $response->getReservationId(),
        ];
    }

    /**
     * Release a previous stock reservation.
     */
    public function releaseStock(string $reservationId): array
    {
        $request = new ReleaseStockRequest();
        $request->setReservationId($reservationId);

        $result = $this->client
            ->ReleaseStock($request, [], ['timeout' => 5000000])
            ->wait();

        /** @var ReleaseStockResponse $response */
        $response = $result[0];
        $status = $result[1];

        if ($status->code !== 0) {
            throw new \RuntimeException(
                'Inventory service unavailable: ' . $status->details
            );
        }

        return [
            'success' => $response->getSuccess(),
            'message' => $response->getMessage(),
        ];
    }
}
