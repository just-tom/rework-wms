<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;

test('it returns the orders index page', function () {
    $product = Product::factory()->create();
    $order = Order::factory()->create([
        'order_status' => OrderStatus::Placed,
        'total' => 3000,
    ]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => 1500,
        'total' => 3000,
    ]);

    $response = $this->get(route('orders.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Orders/Index')
        ->has('orders.data', 1)
        ->has('orders.data.0', fn ($order) => $order
            ->has('uuid')
            ->has('status.label')
            ->has('status.value')
            ->has('total.amount')
            ->has('total.formatted')
            ->has('productTitle')
            ->has('quantity')
            ->has('warehouseName')
            ->has('createdAt')
            ->has('canDispatch')
            ->has('canCancel')
        )
    );
});

test('it paginates orders', function () {
    Order::factory()->count(55)->create([
        'order_status' => OrderStatus::Placed,
        'total' => 1000,
    ]);

    $response = $this->get(route('orders.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('orders.data', 20)
        ->where('orders.meta.total', 55)
        ->where('orders.meta.last_page', 3)
    );
});

test('it orders by latest first', function () {
    $older = Order::factory()->create([
        'order_status' => OrderStatus::Placed,
        'total' => 1000,
        'created_at' => now()->subDay(),
    ]);
    $newer = Order::factory()->create([
        'order_status' => OrderStatus::Placed,
        'total' => 2000,
        'created_at' => now(),
    ]);

    $response = $this->get(route('orders.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('orders.data.0.uuid', $newer->uuid)
        ->where('orders.data.1.uuid', $older->uuid)
    );
});
