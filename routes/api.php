
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Modules\Auth\Controllers\AuthController;
use App\Modules\Users\Controllers\AdminController;
use App\Modules\Products\Controllers\ProductController;
use App\Modules\Orders\Controllers\OrderController;
use App\Modules\Notifications\Controllers\NotificationController;

Route::prefix('v1')->group(function () {

    // Authentication
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

    // Protected routes
    Route::middleware('auth:api')->group(function () {

        // Authentication
        Route::post('/logout', [AuthController::class, 'logout']);

        // User
        Route::get('/user', function (Request $request) {
            return response()->json([
                'data' => $request->user(),
            ], 200);
        });

        // Products
        Route::get('/products', [ProductController::class, 'index']);

        // Orders
        Route::get('/orders', [OrderController::class, 'index']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index']);

        Route::patch('/notifications/{id}/read', [
            NotificationController::class,
            'markAsRead',
        ]);

        Route::patch('/notifications/read-all', [
            NotificationController::class,
            'markAllAsRead',
        ]);

        // Admin
        Route::middleware('admin')->prefix('admin')->group(function () {

            Route::post('/users', [AdminController::class, 'store']);

            Route::post('/products', [ProductController::class, 'store']);

            Route::patch('/orders/{order}/status', [
                OrderController::class,
                'updateStatus',
            ]);

        });

    });

});
