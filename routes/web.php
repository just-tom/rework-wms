<?php

use App\Http\Controllers\Orders\CreateOrderController;
use App\Http\Controllers\Orders\StoreOrderController;
use App\Http\Controllers\Stock\GetStockOverviewController;
use Illuminate\Support\Facades\Route;

Route::get('/', CreateOrderController::class)->name('home');
Route::post('/orders', StoreOrderController::class)->name('orders.store');
Route::get('/stock', GetStockOverviewController::class)->name('stock.index');
