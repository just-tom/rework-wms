<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Managers\StockManager;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\WarehouseStock;

it('sums only placed order quantities for allocated to orders', function () {
    $product = Product::factory()->create();

    OrderItem::factory()->for(Order::factory()->state(['order_status' => OrderStatus::Placed]))->create([
        'product_id' => $product->id,
        'quantity' => 5,
    ]);

    OrderItem::factory()->for(Order::factory()->state(['order_status' => OrderStatus::Placed]))->create([
        'product_id' => $product->id,
        'quantity' => 3,
    ]);

    OrderItem::factory()->for(Order::factory()->state(['order_status' => OrderStatus::Dispatched]))->create([
        'product_id' => $product->id,
        'quantity' => 10,
    ]);

    OrderItem::factory()->for(Order::factory()->state(['order_status' => OrderStatus::Cancelled]))->create([
        'product_id' => $product->id,
        'quantity' => 7,
    ]);

    expect(StockManager::for($product)->allocatedToOrders())->toBe(8);
});

it('returns zero allocated to orders when product has no orders', function () {
    $product = Product::factory()->create();

    expect(StockManager::for($product)->allocatedToOrders())->toBe(0);
});

it('calculates physical quantity as warehouse quantity plus allocated to orders', function () {
    $product = Product::factory()->create();

    WarehouseStock::factory()->create(['product_id' => $product->id, 'quantity' => 20, 'threshold' => 5]);
    WarehouseStock::factory()->create(['product_id' => $product->id, 'quantity' => 10, 'threshold' => 3]);

    OrderItem::factory()->for(Order::factory()->state(['order_status' => OrderStatus::Placed]))->create([
        'product_id' => $product->id,
        'quantity' => 4,
    ]);

    expect(StockManager::for($product)->physicalQuantity())->toBe(34);
});

it('sums thresholds across all warehouses', function () {
    $product = Product::factory()->create();

    WarehouseStock::factory()->create(['product_id' => $product->id, 'threshold' => 5]);
    WarehouseStock::factory()->create(['product_id' => $product->id, 'threshold' => 10]);
    WarehouseStock::factory()->create(['product_id' => $product->id, 'threshold' => 3]);

    expect(StockManager::for($product)->totalThreshold())->toBe(18);
});

it('calculates immediate despatch as warehouse quantity minus total threshold', function () {
    $product = Product::factory()->create();

    WarehouseStock::factory()->create(['product_id' => $product->id, 'quantity' => 50, 'threshold' => 10]);
    WarehouseStock::factory()->create(['product_id' => $product->id, 'quantity' => 30, 'threshold' => 5]);

    expect(StockManager::for($product)->immediateDespatch())->toBe(65);
});

it('returns negative immediate despatch when over-reserved', function () {
    $product = Product::factory()->create();

    WarehouseStock::factory()->create(['product_id' => $product->id, 'quantity' => 5, 'threshold' => 20]);
    WarehouseStock::factory()->create(['product_id' => $product->id, 'quantity' => 3, 'threshold' => 15]);

    expect(StockManager::for($product)->immediateDespatch())->toBe(-27);
});
