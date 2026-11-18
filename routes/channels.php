<?php

use App\Modules\Orders\Models\Order;
use Illuminate\Support\Facades\Broadcast;

// Default private channel: each user can access their own channel.
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Private order channel: only the order owner can subscribe.
Broadcast::channel('orders.{orderId}', function ($user, $orderId) {
    $order = Order::find($orderId);

    return $order !== null
        && (int) $order->user_id === (int) $user->id;
});

// Admin-only channel: only administrators can subscribe.
Broadcast::channel('admin.orders', function ($user) {
    return $user->isAdmin();
});
