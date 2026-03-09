<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Models\Order;

test('it dispatches a placed order', function () {
    $order = Order::factory()->create([
        'order_status' => OrderStatus::Placed,
        'total' => 1000,
    ]);

    $response = $this->patch(route('orders.dispatch', $order->uuid));

    $response->assertRedirect()
        ->assertInertiaFlash('success', 'Order dispatched successfully.');

    expect($order->fresh()->order_status)->toBe(OrderStatus::Dispatched);
});

test('it cannot dispatch a cancelled order', function () {
    $order = Order::factory()->create([
        'order_status' => OrderStatus::Cancelled,
        'total' => 1000,
    ]);

    $response = $this->patch(route('orders.dispatch', $order->uuid));

    $response->assertStatus(409);

    expect($order->fresh()->order_status)->toBe(OrderStatus::Cancelled);
});

test('it cannot dispatch an already dispatched order', function () {
    $order = Order::factory()->create([
        'order_status' => OrderStatus::Dispatched,
        'total' => 1000,
    ]);

    $response = $this->patch(route('orders.dispatch', $order->uuid));

    $response->assertStatus(409);

    expect($order->fresh()->order_status)->toBe(OrderStatus::Dispatched);
});
