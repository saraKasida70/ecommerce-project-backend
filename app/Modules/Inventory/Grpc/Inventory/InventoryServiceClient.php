<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Inventory;

/**
 */
class InventoryServiceClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * Get available stock for a product
     * @param \Inventory\GetStockRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function GetStock(\Inventory\GetStockRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/inventory.InventoryService/GetStock',
        $argument,
        ['\Inventory\GetStockResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * Reserve all requested products atomically
     * @param \Inventory\ReserveStockRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function ReserveStock(\Inventory\ReserveStockRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/inventory.InventoryService/ReserveStock',
        $argument,
        ['\Inventory\ReserveStockResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * Release previously reserved products
     * @param \Inventory\ReleaseStockRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function ReleaseStock(\Inventory\ReleaseStockRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/inventory.InventoryService/ReleaseStock',
        $argument,
        ['\Inventory\ReleaseStockResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * Add stock or increase existing stock
     * @param \Inventory\AddStockRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function AddStock(\Inventory\AddStockRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/inventory.InventoryService/AddStock',
        $argument,
        ['\Inventory\AddStockResponse', 'decode'],
        $metadata, $options);
    }

}
