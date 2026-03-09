<?php

use App\Http\Controllers\Orders\CancelOrderController;
use App\Http\Controllers\Orders\CreateOrderController;
use App\Http\Controllers\Orders\DispatchOrderController;
use App\Http\Controllers\Orders\GetOrdersController;
use App\Http\Controllers\Orders\StoreOrderController;
use App\Http\Controllers\Stock\GetStockOverviewController;
use Illuminate\Support\Facades\Route;

Route::get('/', CreateOrderController::class)->name('home');
Route::post('/orders', StoreOrderController::class)->name('orders.store');
Route::get('/orders', GetOrdersController::class)->name('orders.index');
Route::patch('/orders/{order:uuid}/dispatch', DispatchOrderController::class)->name('orders.dispatch');
Route::patch('/orders/{order:uuid}/cancel', CancelOrderController::class)->name('orders.cancel');
Route::get('/stock', GetStockOverviewController::class)->name('stock.index');
