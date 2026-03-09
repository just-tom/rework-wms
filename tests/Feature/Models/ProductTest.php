<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;

test('inStock scope includes products with stock above threshold', function () {
    $product = Product::factory()->create();
    WarehouseStock::factory()->create([
        'product_id' => $product->id,
        'quantity' => 10,
        'threshold' => 5,
    ]);

    $results = Product::inStock()->pluck('id')->all();

    expect($results)->toBe([$product->id]);
});

test('inStock scope excludes products with stock equal to threshold', function () {
    $product = Product::factory()->create();
    WarehouseStock::factory()->create([
        'product_id' => $product->id,
        'quantity' => 5,
        'threshold' => 5,
    ]);

    $results = Product::inStock()->pluck('id')->all();

    expect($results)->toBeEmpty();
});

test('inStock scope excludes products with stock below threshold', function () {
    $product = Product::factory()->create();
    WarehouseStock::factory()->create([
        'product_id' => $product->id,
        'quantity' => 3,
        'threshold' => 8,
    ]);

    $results = Product::inStock()->pluck('id')->all();

    expect($results)->toBeEmpty();
});

test('inStock scope excludes products with no stock record', function () {
    Product::factory()->create();

    $results = Product::inStock()->pluck('id')->all();

    expect($results)->toBeEmpty();
});

test('inStock scope includes product when at least one warehouse has stock above threshold', function () {
    $product = Product::factory()->create();
    $warehouseA = Warehouse::factory()->create();
    $warehouseB = Warehouse::factory()->create();

    WarehouseStock::factory()->create([
        'warehouse_id' => $warehouseA->id,
        'product_id' => $product->id,
        'quantity' => 3,
        'threshold' => 3,
    ]);
    WarehouseStock::factory()->create([
        'warehouse_id' => $warehouseB->id,
        'product_id' => $product->id,
        'quantity' => 10,
        'threshold' => 5,
    ]);

    $results = Product::inStock()->pluck('id')->all();

    expect($results)->toBe([$product->id]);
});
