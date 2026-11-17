<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/products');

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::get('/products', function () {
    return view('products.index');
})->name('products.index');

Route::get('/cart', function () {
    return view('cart.index');
})->name('cart.index');

Route::get('/checkout', function () {
    return view('checkout.index');
})->name('checkout.index');

Route::get('/orders', function () {
    return view('orders.index');
})->name('orders.index');

Route::get('/notifications', function () {
    return view('notifications.index');
})->name('notifications.index');

Route::get('/admin', function () {
    return view('admin.dashboard');
})->name('admin.dashboard');

Route::get('/admin/orders', function () {
    return view('admin.orders');
})->name('admin.orders');

Route::get('/admin/inventory', function () {
    return view('admin.inventory');
})->name('admin.inventory');

Route::get('/admin/users', function () {
    return view('admin.users');
})->name('admin.users');

Route::get('/inventory/test', function () {
    return view('inventory.test');
})->name('inventory.test');
