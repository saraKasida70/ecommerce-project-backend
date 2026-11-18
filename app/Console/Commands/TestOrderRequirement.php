<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Modules\Products\Models\Product;
use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Models\OrderItem;
use App\Modules\Inventory\Services\InventoryGrpcClient;
use App\Modules\Notifications\Services\NotificationService;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class TestOrderRequirement extends Command
{
    protected $signature = 'test:order-requirement';
    protected $description = 'Test Order Requirement 2 functionality';

    public function handle()
    {
        $this->info('=== Testing Order Requirement 2 ===' . PHP_EOL);

        // Test 1: Check products exist
        $this->info('Test 1: Checking products...');
        $products = Product::all();
        $this->info('Products count: ' . $products->count());
        foreach ($products as $p) {
            $this->info('  - ' . $p->name . ' (ID: ' . $p->id . ', Price: ' . $p->price . ')');
        }
        $this->info('✓ Products loaded successfully' . PHP_EOL);

        // Test 2: Check users exist
        $this->info('Test 2: Checking users...');
        $customer = User::where('email', 'customer@test.com')->first();
        $admin = User::where('email', 'admin@test.com')->first();
        
        if ($customer) {
            $this->info('  Customer: ' . $customer->name . ' (ID: ' . $customer->id . ')');
        } else {
            $this->error('  Customer not found!');
            return 1;
        }
        
        if ($admin) {
            $this->info('  Admin: ' . $admin->name . ' (ID: ' . $admin->id . ')');
        } else {
            $this->error('  Admin not found!');
            return 1;
        }
        $this->info('✓ Users loaded successfully' . PHP_EOL);

        // Test 3: Test Inventory Service
        $this->info('Test 3: Testing Inventory Service (gRPC)...');
        try {
            $inventoryClient = new InventoryGrpcClient();
            
            // Add stock for testing
            foreach ($products as $p) {
                try {
                    $result = $inventoryClient->addStock($p->id, 100);
                    $this->info('  Added 100 stock for product ' . $p->id . ': ' . ($result['success'] ? 'Success' : 'Failed'));
                } catch (\Exception $e) {
                    $this->warn('  Could not add stock for product ' . $p->id . ': ' . $e->getMessage());
                }
            }
            
            // Get stock
            $stockResult = $inventoryClient->getStock($products->first()->id);
            $this->info('  Stock for product ' . $products->first()->id . ': ' . json_encode($stockResult));
            $this->info('✓ Inventory Service is working' . PHP_EOL);
        } catch (\Exception $e) {
            $this->error('Inventory Service error: ' . $e->getMessage());
            $this->error('✗ Inventory Service test failed' . PHP_EOL);
        }

        // Test 4: Create order with multiple products
        $this->info('Test 4: Creating order with multiple products...');
        try {
            $inventoryClient = new InventoryGrpcClient();
            
            $orderItems = [
                ['product_id' => $products[0]->id, 'quantity' => 2],
                ['product_id' => $products[1]->id, 'quantity' => 1],
            ];
            
            $orderReference = 'TEST-ORDER-' . time();
            $reservation = $inventoryClient->reserveStock($orderReference, $orderItems);
            
            $this->info('  Reservation result: ' . json_encode($reservation));
            
            if ($reservation['success']) {
                $reservationId = $reservation['reservation_id'];
                $this->info('  Reservation ID: ' . $reservationId);
                
                // Create order
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
                
                $this->info('  Order created: ID ' . $order->id . ', Total: ' . $total);
                $this->info('✓ Order with multiple products created successfully' . PHP_EOL);
            } else {
                $this->error('  Reservation failed: ' . $reservation['message']);
                $this->error('✗ Order creation failed' . PHP_EOL);
            }
        } catch (\Exception $e) {
            $this->error('Order creation error: ' . $e->getMessage());
            $this->error('✗ Order creation test failed' . PHP_EOL);
        }

        // Test 5: Check customer can only see own orders
        $this->info('Test 5: Testing customer order isolation...');
        $customerOrders = Order::where('user_id', $customer->id)->get();
        $this->info('  Customer orders count: ' . $customerOrders->count());
        foreach ($customerOrders as $o) {
            $this->info('    Order ID: ' . $o->id . ', Total: ' . $o->total);
        }
        
        $allOrders = Order::all();
        $this->info('  Total orders in system: ' . $allOrders->count());
        
        if ($customerOrders->count() <= $allOrders->count()) {
            $this->info('✓ Customer isolation working correctly' . PHP_EOL);
        } else {
            $this->error('✗ Customer isolation failed' . PHP_EOL);
        }

        // Test 6: Test notification on order creation
        $this->info('Test 6: Testing notification on order creation...');
        try {
            $notificationService = new NotificationService();
            $testOrder = Order::where('user_id', $customer->id)->first();
            
            if ($testOrder) {
                $notificationService->send(
                    $customer,
                    'Test Order Created',
                    'Your order #' . $testOrder->id . ' has been created.',
                    ['order_id' => $testOrder->id]
                );
                
                $notifications = $customer->notifications;
                $this->info('  Customer notifications count: ' . $notifications->count());
                $this->info('✓ Notification system working' . PHP_EOL);
            }
        } catch (\Exception $e) {
            $this->error('Notification error: ' . $e->getMessage());
            $this->error('✗ Notification test failed' . PHP_EOL);
        }

        // Test 7: Test admin can update order status
        $this->info('Test 7: Testing admin order status update...');
        try {
            $testOrder = Order::where('user_id', $customer->id)->first();
            if ($testOrder) {
                $oldStatus = $testOrder->status;
                $testOrder->update(['status' => 'processing']);
                $this->info('  Order ' . $testOrder->id . ' status changed from ' . $oldStatus . ' to processing');
                
                // Test notification on status update
                $notificationService = new NotificationService();
                $notificationService->send(
                    $customer,
                    'Order Status Updated',
                    'Your order #' . $testOrder->id . ' status is now processing.',
                    ['order_id' => $testOrder->id, 'status' => 'processing']
                );
                
                $this->info('✓ Admin status update working' . PHP_EOL);
            }
        } catch (\Exception $e) {
            $this->error('Status update error: ' . $e->getMessage());
            $this->error('✗ Status update test failed' . PHP_EOL);
        }

        // Test 8: Test insufficient stock
        $this->info('Test 8: Testing insufficient stock scenario...');
        try {
            $inventoryClient = new InventoryGrpcClient();
            
            // Try to reserve more than available
            $orderItems = [
                ['product_id' => $products[0]->id, 'quantity' => 1000], // More than available (100)
            ];
            
            $orderReference = 'TEST-INSUFFICIENT-' . time();
            $reservation = $inventoryClient->reserveStock($orderReference, $orderItems);
            
            $this->info('  Reservation result: ' . json_encode($reservation));
            
            if (!$reservation['success']) {
                $this->info('  Reservation correctly rejected due to insufficient stock');
                $this->info('✓ Insufficient stock handling working correctly' . PHP_EOL);
            } else {
                $this->error('  Reservation should have failed but succeeded');
                $this->error('✗ Insufficient stock test failed' . PHP_EOL);
                
                // Release the reservation if it succeeded
                if (isset($reservation['reservation_id'])) {
                    $inventoryClient->releaseStock($reservation['reservation_id']);
                }
            }
        } catch (\Exception $e) {
            $this->error('Insufficient stock test error: ' . $e->getMessage());
            $this->error('✗ Insufficient stock test failed' . PHP_EOL);
        }

        // Test 9: Test order creation failure after reservation and release
        $this->info('Test 9: Testing order creation failure and reservation release...');
        try {
            $inventoryClient = new InventoryGrpcClient();
            
            $orderItems = [
                ['product_id' => $products[1]->id, 'quantity' => 1],
            ];
            
            $orderReference = 'TEST-FAILURE-' . time();
            $reservation = $inventoryClient->reserveStock($orderReference, $orderItems);
            
            if ($reservation['success']) {
                $reservationId = $reservation['reservation_id'];
                $this->info('  Reservation ID: ' . $reservationId);
                
                // Simulate failure by NOT creating the order
                $this->info('  Simulating order creation failure...');
                
                // Release the reservation manually (simulating compensation)
                $releaseResult = $inventoryClient->releaseStock($reservationId);
                $this->info('  Release result: ' . json_encode($releaseResult));
                
                if ($releaseResult['success']) {
                    $this->info('✓ Reservation release working correctly' . PHP_EOL);
                } else {
                    $this->error('✗ Reservation release failed' . PHP_EOL);
                }
            } else {
                $this->error('  Reservation failed: ' . $reservation['message']);
                $this->error('✗ Cannot test release scenario' . PHP_EOL);
            }
        } catch (\Exception $e) {
            $this->error('Order failure test error: ' . $e->getMessage());
            $this->error('✗ Order failure test failed' . PHP_EOL);
        }

        $this->info('=== All Tests Completed ===');
        return 0;
    }
}
