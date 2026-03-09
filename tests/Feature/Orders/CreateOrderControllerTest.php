<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\WarehouseStock;
use Inertia\Testing\AssertableInertia as Assert;

test('it renders the place order page', function () {
    $response = $this->get(route('home'));

    $response->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Orders/PlaceOrder')
        );
});

test('it provides products as a prop', function () {
    $products = Product::factory()->count(3)->create();
    $products->each(fn (Product $product) => WarehouseStock::factory()->create([
        'product_id' => $product->id,
        'quantity' => 10,
        'threshold' => 0,
    ]));

    $response = $this->get(route('home'));

    $response->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Orders/PlaceOrder')
            ->has('products', 3)
        );
});

test('products do not expose id and include structured price', function () {
    $product = Product::factory()->create(['price' => 1250]);
    WarehouseStock::factory()->create([
        'product_id' => $product->id,
        'quantity' => 10,
        'threshold' => 0,
    ]);

    $response = $this->get(route('home'));

    $response->assertInertia(fn (Assert $page) => $page
        ->missing('products.0.id')
        ->has('products.0.uuid')
        ->where('products.0.price.amount', 1250)
        ->where('products.0.price.formatted', '£12.50')
    );
});

test('it excludes out-of-stock products', function () {
    $inStock = Product::factory()->create(['title' => 'In Stock Product']);
    WarehouseStock::factory()->create([
        'product_id' => $inStock->id,
        'quantity' => 5,
        'threshold' => 2,
    ]);

    $thresholdExceedsQuantity = Product::factory()->create(['title' => 'Threshold Exceeds Quantity']);
    WarehouseStock::factory()->create([
        'product_id' => $thresholdExceedsQuantity->id,
        'quantity' => 2,
        'threshold' => 5,
    ]);

    $noSellableStock = Product::factory()->create(['title' => 'No Sellable Stock']);
    WarehouseStock::factory()->create([
        'product_id' => $noSellableStock->id,
        'quantity' => 3,
        'threshold' => 3,
    ]);

    Product::factory()->create(['title' => 'No Stock Record']);

    $response = $this->get(route('home'));

    $response->assertInertia(fn (Assert $page) => $page
        ->has('products', 1)
        ->where('products.0.title', 'In Stock Product')
    );
});
