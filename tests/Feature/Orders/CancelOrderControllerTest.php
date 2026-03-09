<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\WarehouseStock;

test('it cancels a placed order and restores stock', function () {
    $product = Product::factory()->create();
    $warehouseStock = WarehouseStock::factory()->create([
        'product_id' => $product->id,
        'quantity' => 5,
    ]);

    $order = Order::factory()->create([
        'order_status' => OrderStatus::Placed,
        'total' => 1000,
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'warehouse_id' => $warehouseStock->warehouse_id,
        'quantity' => 3,
    ]);

    $response = $this->patch(route('orders.cancel', $order->uuid));

    $response->assertRedirect()
        ->assertInertiaFlash('success', 'Order cancelled successfully.');

    expect($order->fresh()->order_status)->toBe(OrderStatus::Cancelled);
    expect($warehouseStock->fresh()->quantity)->toBe(8);
});

test('it cancels a dispatched order and restores stock', function () {
    $product = Product::factory()->create();
    $warehouseStock = WarehouseStock::factory()->create([
        'product_id' => $product->id,
        'quantity' => 10,
    ]);

    $order = Order::factory()->create([
        'order_status' => OrderStatus::Dispatched,
        'total' => 1000,
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'warehouse_id' => $warehouseStock->warehouse_id,
        'quantity' => 2,
    ]);

    $response = $this->patch(route('orders.cancel', $order->uuid));

    $response->assertRedirect()
        ->assertInertiaFlash('success', 'Order cancelled successfully.');

    expect($order->fresh()->order_status)->toBe(OrderStatus::Cancelled);
    expect($warehouseStock->fresh()->quantity)->toBe(12);
});

test('it cannot cancel an already cancelled order', function () {
    $product = Product::factory()->create();
    $warehouseStock = WarehouseStock::factory()->create([
        'product_id' => $product->id,
        'quantity' => 5,
    ]);

    $order = Order::factory()->create([
        'order_status' => OrderStatus::Cancelled,
        'total' => 1000,
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'warehouse_id' => $warehouseStock->warehouse_id,
        'quantity' => 3,
    ]);

    $response = $this->patch(route('orders.cancel', $order->uuid));

    $response->assertStatus(409);

    expect($order->fresh()->order_status)->toBe(OrderStatus::Cancelled);
    expect($warehouseStock->fresh()->quantity)->toBe(5);
});
