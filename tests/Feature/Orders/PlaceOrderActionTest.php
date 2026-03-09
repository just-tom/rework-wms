<?php

declare(strict_types=1);

use App\Actions\Orders\PlaceOrderAction;
use App\Data\StoreOrderData;
use App\Enums\OrderStatus;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\OutOfStockException;
use App\Models\Order;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;

test('it creates an order and deducts stock', function () {
    $product = Product::factory()->create(['price' => 2000]);
    $warehouse = Warehouse::factory()->create();
    WarehouseStock::factory()->create([
        'warehouse_id' => $warehouse->id,
        'product_id' => $product->id,
        'quantity' => 20,
        'threshold' => 0,
    ]);

    $data = StoreOrderData::from([
        'productUuid' => $product->uuid,
        'quantity' => 3,
    ]);

    $action = new PlaceOrderAction;
    $order = $action->execute($data);

    expect($order)->toBeInstanceOf(Order::class);
    expect($order->order_status)->toBe(OrderStatus::Placed);
    expect($order->getRawOriginal('total'))->toBe(6000);
    expect($order->items)->toHaveCount(1);

    $stock = WarehouseStock::where('product_id', $product->id)->first();
    expect($stock->quantity)->toBe(17);
});

test('it deducts from the warehouse with the highest stock', function () {
    $product = Product::factory()->create(['price' => 500]);
    $warehouseA = Warehouse::factory()->create();
    $warehouseB = Warehouse::factory()->create();

    WarehouseStock::factory()->create([
        'warehouse_id' => $warehouseA->id,
        'product_id' => $product->id,
        'quantity' => 30,
        'threshold' => 0,
    ]);
    WarehouseStock::factory()->create([
        'warehouse_id' => $warehouseB->id,
        'product_id' => $product->id,
        'quantity' => 80,
        'threshold' => 0,
    ]);

    $data = StoreOrderData::from([
        'productUuid' => $product->uuid,
        'quantity' => 10,
    ]);

    $action = new PlaceOrderAction;
    $action->execute($data);

    $stockA = WarehouseStock::where('warehouse_id', $warehouseA->id)->first();
    $stockB = WarehouseStock::where('warehouse_id', $warehouseB->id)->first();

    expect($stockB->quantity)->toBe(70);
    expect($stockA->quantity)->toBe(30);
});

test('it throws InsufficientStockException when stock is too low', function () {
    $product = Product::factory()->create();
    $warehouse = Warehouse::factory()->create();
    WarehouseStock::factory()->create([
        'warehouse_id' => $warehouse->id,
        'product_id' => $product->id,
        'quantity' => 5,
        'threshold' => 0,
    ]);

    $data = StoreOrderData::from([
        'productUuid' => $product->uuid,
        'quantity' => 10,
    ]);

    $action = new PlaceOrderAction;
    $action->execute($data);
})->throws(InsufficientStockException::class, 'Only 5 units available.');

test('it throws OutOfStockException when no stock exists', function () {
    $product = Product::factory()->create();

    $data = StoreOrderData::from([
        'productUuid' => $product->uuid,
        'quantity' => 1,
    ]);

    $action = new PlaceOrderAction;
    $action->execute($data);
})->throws(OutOfStockException::class, 'This product is currently out of stock.');

test('it does not create an order when stock is insufficient', function () {
    $product = Product::factory()->create();
    $warehouse = Warehouse::factory()->create();
    WarehouseStock::factory()->create([
        'warehouse_id' => $warehouse->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'threshold' => 0,
    ]);

    $data = StoreOrderData::from([
        'productUuid' => $product->uuid,
        'quantity' => 5,
    ]);

    $action = new PlaceOrderAction;

    try {
        $action->execute($data);
    } catch (InsufficientStockException|OutOfStockException) {
    }

    expect(Order::count())->toBe(0);
});

test('it deducts from sellable stock respecting threshold', function () {
    $product = Product::factory()->create(['price' => 1000]);
    $warehouse = Warehouse::factory()->create();
    WarehouseStock::factory()->create([
        'warehouse_id' => $warehouse->id,
        'product_id' => $product->id,
        'quantity' => 15,
        'threshold' => 5,
    ]);

    $data = StoreOrderData::from([
        'productUuid' => $product->uuid,
        'quantity' => 3,
    ]);

    $action = new PlaceOrderAction;
    $order = $action->execute($data);

    expect($order)->toBeInstanceOf(Order::class);

    $stock = WarehouseStock::where('product_id', $product->id)->first();
    expect($stock->quantity)->toBe(12);
});

test('it throws OutOfStockException when quantity equals threshold', function () {
    $product = Product::factory()->create();
    $warehouse = Warehouse::factory()->create();
    WarehouseStock::factory()->create([
        'warehouse_id' => $warehouse->id,
        'product_id' => $product->id,
        'quantity' => 5,
        'threshold' => 5,
    ]);

    $data = StoreOrderData::from([
        'productUuid' => $product->uuid,
        'quantity' => 1,
    ]);

    $action = new PlaceOrderAction;
    $action->execute($data);
})->throws(OutOfStockException::class);

test('it throws InsufficientStockException when ordering more than sellable amount', function () {
    $product = Product::factory()->create();
    $warehouse = Warehouse::factory()->create();
    WarehouseStock::factory()->create([
        'warehouse_id' => $warehouse->id,
        'product_id' => $product->id,
        'quantity' => 10,
        'threshold' => 7,
    ]);

    $data = StoreOrderData::from([
        'productUuid' => $product->uuid,
        'quantity' => 5,
    ]);

    $action = new PlaceOrderAction;
    $action->execute($data);
})->throws(InsufficientStockException::class, 'Only 3 units available.');

test('it picks warehouse with highest sellable stock not highest raw quantity', function () {
    $product = Product::factory()->create(['price' => 500]);
    $warehouseA = Warehouse::factory()->create();
    $warehouseB = Warehouse::factory()->create();

    WarehouseStock::factory()->create([
        'warehouse_id' => $warehouseA->id,
        'product_id' => $product->id,
        'quantity' => 100,
        'threshold' => 95,
    ]);
    WarehouseStock::factory()->create([
        'warehouse_id' => $warehouseB->id,
        'product_id' => $product->id,
        'quantity' => 50,
        'threshold' => 0,
    ]);

    $data = StoreOrderData::from([
        'productUuid' => $product->uuid,
        'quantity' => 10,
    ]);

    $action = new PlaceOrderAction;
    $action->execute($data);

    $stockA = WarehouseStock::where('warehouse_id', $warehouseA->id)->first();
    $stockB = WarehouseStock::where('warehouse_id', $warehouseB->id)->first();

    expect($stockA->quantity)->toBe(100);
    expect($stockB->quantity)->toBe(40);
});
