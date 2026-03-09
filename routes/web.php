<?php

use App\Http\Controllers\Orders\CreateOrderController;
use App\Http\Controllers\Orders\StoreOrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', CreateOrderController::class)->name('home');
Route::post('/orders', StoreOrderController::class)->name('orders.store');
