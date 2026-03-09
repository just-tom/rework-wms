<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Managers\StockManager;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Warehouse;
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

it('scopes allocated to orders to a specific warehouse using in()', function () {
    $product = Product::factory()->create();
    $warehouseA = Warehouse::factory()->create();
    $warehouseB = Warehouse::factory()->create();

    OrderItem::factory()->for(Order::factory()->state(['order_status' => OrderStatus::Placed]))->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouseA->id,
        'quantity' => 5,
    ]);

    OrderItem::factory()->for(Order::factory()->state(['order_status' => OrderStatus::Placed]))->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouseB->id,
        'quantity' => 3,
    ]);

    OrderItem::factory()->for(Order::factory()->state(['order_status' => OrderStatus::Placed]))->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouseA->id,
        'quantity' => 2,
    ]);

    expect(StockManager::for($product)->in($warehouseA)->allocatedToOrders())->toBe(7)
        ->and(StockManager::for($product)->in($warehouseB)->allocatedToOrders())->toBe(3)
        ->and(StockManager::for($product)->allocatedToOrders())->toBe(10);
});

it('scopes immediate despatch to a specific warehouse using in()', function () {
    $product = Product::factory()->create();
    $warehouseA = Warehouse::factory()->create();
    $warehouseB = Warehouse::factory()->create();

    WarehouseStock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouseA->id,
        'quantity' => 50,
        'threshold' => 10,
    ]);

    WarehouseStock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouseB->id,
        'quantity' => 30,
        'threshold' => 5,
    ]);

    expect(StockManager::for($product)->in($warehouseA)->immediateDespatch())->toBe(40)
        ->and(StockManager::for($product)->in($warehouseB)->immediateDespatch())->toBe(25)
        ->and(StockManager::for($product)->immediateDespatch())->toBe(65);
});

it('does not mutate the original instance when using in()', function () {
    $product = Product::factory()->create();
    $warehouse = Warehouse::factory()->create();

    WarehouseStock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'quantity' => 20,
        'threshold' => 5,
    ]);

    WarehouseStock::factory()->create([
        'product_id' => $product->id,
        'quantity' => 10,
        'threshold' => 3,
    ]);

    $manager = StockManager::for($product);

    // Calling in() should not affect the original manager
    $scoped = $manager->in($warehouse);

    expect($scoped->immediateDespatch())->toBe(15)
        ->and($manager->immediateDespatch())->toBe(22);
});

it('accepts a Warehouse model instance for in()', function () {
    $product = Product::factory()->create();
    $warehouse = Warehouse::factory()->create();

    WarehouseStock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'quantity' => 20,
        'threshold' => 5,
    ]);

    expect(StockManager::for($product)->in($warehouse)->immediateDespatch())->toBe(15);
});
