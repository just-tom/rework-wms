<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;

it('renders the stock overview page', function () {
    $response = $this->get(route('stock.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page->component('Stock/Index'));
});

it('returns products with correct stock aggregate values', function () {
    $product = Product::factory()->create(['title' => 'Test Widget']);
    $warehouse = Warehouse::factory()->create(['name' => 'London']);

    WarehouseStock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'quantity' => 100,
        'threshold' => 15,
    ]);

    OrderItem::factory()->for(Order::factory()->state(['order_status' => OrderStatus::Placed]))->create([
        'product_id' => $product->id,
        'quantity' => 8,
    ]);

    $response = $this->get(route('stock.index'));

    $response->assertInertia(fn ($page) => $page
        ->component('Stock/Index')
        ->has('products.data', 1)
        ->where('products.data.0.uuid', $product->uuid)
        ->where('products.data.0.title', 'Test Widget')
        ->where('products.data.0.allocatedToOrders', 8)
        ->where('products.data.0.physicalQuantity', 108)
        ->where('products.data.0.totalThreshold', 15)
        ->where('products.data.0.immediateDespatch', 85)
    );
});

it('includes warehouse breakdown per product', function () {
    $product = Product::factory()->create();
    $london = Warehouse::factory()->create(['name' => 'London']);
    $manchester = Warehouse::factory()->create(['name' => 'Manchester']);

    WarehouseStock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $london->id,
        'quantity' => 60,
        'threshold' => 10,
    ]);

    WarehouseStock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $manchester->id,
        'quantity' => 25,
        'threshold' => 5,
    ]);

    $response = $this->get(route('stock.index'));

    $response->assertInertia(fn ($page) => $page
        ->has('products.data.0.warehouses', 2)
        ->where('products.data.0.warehouses.0.name', 'London')
        ->where('products.data.0.warehouses.0.quantity', 60)
        ->where('products.data.0.warehouses.0.threshold', 10)
        ->where('products.data.0.warehouses.0.allocatedToOrders', 0)
        ->where('products.data.0.warehouses.0.immediateDespatch', 50)
        ->where('products.data.0.warehouses.1.name', 'Manchester')
        ->where('products.data.0.warehouses.1.quantity', 25)
        ->where('products.data.0.warehouses.1.threshold', 5)
        ->where('products.data.0.warehouses.1.allocatedToOrders', 0)
        ->where('products.data.0.warehouses.1.immediateDespatch', 20)
    );
});

it('shows zeros for product with no warehouse stock', function () {
    Product::factory()->create(['title' => 'Empty Product']);

    $response = $this->get(route('stock.index'));

    $response->assertInertia(fn ($page) => $page
        ->where('products.data.0.allocatedToOrders', 0)
        ->where('products.data.0.physicalQuantity', 0)
        ->where('products.data.0.totalThreshold', 0)
        ->where('products.data.0.immediateDespatch', 0)
        ->where('products.data.0.warehouses', [])
    );
});

it('paginates products at 25 per page', function () {
    Product::factory()->count(75)->create();

    $response = $this->get(route('stock.index'));

    $response->assertInertia(fn ($page) => $page
        ->has('products.data', 25)
        ->where('products.meta.current_page', 1)
        ->where('products.meta.per_page', 25)
        ->where('products.meta.total', 75)
        ->where('products.meta.last_page', 3)
    );
});

it('returns the second page of products', function () {
    Product::factory()->count(75)->create();

    $response = $this->get(route('stock.index', ['page' => 2]));

    $response->assertInertia(fn ($page) => $page
        ->has('products.data', 25)
        ->where('products.meta.current_page', 2)
    );
});

it('includes per-warehouse allocated to orders and immediate despatch', function () {
    $product = Product::factory()->create();
    $london = Warehouse::factory()->create(['name' => 'London']);
    $manchester = Warehouse::factory()->create(['name' => 'Manchester']);

    WarehouseStock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $london->id,
        'quantity' => 50,
        'threshold' => 10,
    ]);

    WarehouseStock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $manchester->id,
        'quantity' => 30,
        'threshold' => 5,
    ]);

    OrderItem::factory()->for(Order::factory()->state(['order_status' => OrderStatus::Placed]))->create([
        'product_id' => $product->id,
        'warehouse_id' => $london->id,
        'quantity' => 7,
    ]);

    OrderItem::factory()->for(Order::factory()->state(['order_status' => OrderStatus::Placed]))->create([
        'product_id' => $product->id,
        'warehouse_id' => $manchester->id,
        'quantity' => 3,
    ]);

    OrderItem::factory()->for(Order::factory()->state(['order_status' => OrderStatus::Dispatched]))->create([
        'product_id' => $product->id,
        'warehouse_id' => $london->id,
        'quantity' => 100,
    ]);

    $response = $this->get(route('stock.index'));

    $response->assertInertia(fn ($page) => $page
        ->where('products.data.0.warehouses.0.name', 'London')
        ->where('products.data.0.warehouses.0.allocatedToOrders', 7)
        ->where('products.data.0.warehouses.0.immediateDespatch', 40)
        ->where('products.data.0.warehouses.1.name', 'Manchester')
        ->where('products.data.0.warehouses.1.allocatedToOrders', 3)
        ->where('products.data.0.warehouses.1.immediateDespatch', 25)
    );
});
