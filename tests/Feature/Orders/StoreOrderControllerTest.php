<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;

test('it validates required fields', function () {
    $response = $this->post(route('orders.store'), []);

    $response->assertSessionHasErrors(['productUuid', 'quantity']);
});

test('it validates productUuid exists', function () {
    $response = $this->post(route('orders.store'), [
        'productUuid' => 'non-existent-uuid',
        'quantity' => 1,
    ]);

    $response->assertSessionHasErrors(['productUuid']);
});

test('it validates quantity is at least 1', function () {
    $product = Product::factory()->create();

    $response = $this->post(route('orders.store'), [
        'productUuid' => $product->uuid,
        'quantity' => 0,
    ]);

    $response->assertSessionHasErrors(['quantity']);
});

test('it creates an order and deducts stock on valid submission', function () {
    $product = Product::factory()->create(['price' => 1500]);
    $warehouse = Warehouse::factory()->create();
    WarehouseStock::factory()->create([
        'warehouse_id' => $warehouse->id,
        'product_id' => $product->id,
        'quantity' => 50,
    ]);

    $response = $this->post(route('orders.store'), [
        'productUuid' => $product->uuid,
        'quantity' => 3,
    ]);

    $response->assertRedirect()
        ->assertInertiaFlash('success', 'Order placed successfully!');

    expect(Order::count())->toBe(1);

    $order = Order::first();
    expect($order->order_status)->toBe(OrderStatus::Placed);
    expect($order->getRawOriginal('total'))->toBe(4500);
    expect($order->items)->toHaveCount(1);

    $item = $order->items->first();
    expect($item->product_id)->toBe($product->id);
    expect($item->getRawOriginal('price'))->toBe(1500);
    expect($item->quantity)->toBe(3);
    expect($item->getRawOriginal('total'))->toBe(4500);

    expect($product->warehouseStocks()->first()->quantity)->toBe(47);
});

test('it selects the warehouse with the most stock', function () {
    $product = Product::factory()->create(['price' => 1000]);
    $warehouseLow = Warehouse::factory()->create();
    $warehouseHigh = Warehouse::factory()->create();

    WarehouseStock::factory()->create([
        'warehouse_id' => $warehouseLow->id,
        'product_id' => $product->id,
        'quantity' => 10,
    ]);
    WarehouseStock::factory()->create([
        'warehouse_id' => $warehouseHigh->id,
        'product_id' => $product->id,
        'quantity' => 100,
    ]);

    $this->post(route('orders.store'), [
        'productUuid' => $product->uuid,
        'quantity' => 5,
    ]);

    $highStock = WarehouseStock::where('warehouse_id', $warehouseHigh->id)
        ->where('product_id', $product->id)
        ->first();
    $lowStock = WarehouseStock::where('warehouse_id', $warehouseLow->id)
        ->where('product_id', $product->id)
        ->first();

    expect($highStock->quantity)->toBe(95);
    expect($lowStock->quantity)->toBe(10);
});

test('it returns validation error when stock is insufficient', function () {
    $product = Product::factory()->create();
    $warehouse = Warehouse::factory()->create();
    WarehouseStock::factory()->create([
        'warehouse_id' => $warehouse->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $response = $this->post(route('orders.store'), [
        'productUuid' => $product->uuid,
        'quantity' => 5,
    ]);

    $response->assertSessionHasErrors(['quantity']);
    expect(Order::count())->toBe(0);
});

test('it returns validation error when no warehouse stock exists', function () {
    $product = Product::factory()->create();

    $response = $this->post(route('orders.store'), [
        'productUuid' => $product->uuid,
        'quantity' => 1,
    ]);

    $response->assertSessionHasErrors(['quantity']);
    expect(Order::count())->toBe(0);
});

test('it preserves old input on validation error', function () {
    $product = Product::factory()->create();

    $response = $this->from(route('home'))->post(route('orders.store'), [
        'productUuid' => $product->uuid,
        'quantity' => 0,
    ]);

    $response->assertRedirect(route('home'))
        ->assertSessionHasErrors(['quantity']);

    expect(session()->getOldInput('productUuid'))->toBe($product->uuid);
    expect(session()->getOldInput('quantity'))->toBe(0);
});
