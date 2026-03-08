<?php

use App\Models\Product;
use App\ValueObjects\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;

test('it casts price to money when retrieving from database', function () {
    $product = Product::factory()->create(['price' => 1999]);

    expect($product->price)
        ->toBeInstanceOf(Money::class)
        ->amount->toBe(1999)
        ->currency->toBe('GBP');
});

test('it stores money object as integer in database', function () {
    $money = new Money(2500);

    $product = Product::factory()->create(['price' => $money]);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'price' => 2500,
    ]);
});

test('it stores raw integer as integer in database', function () {
    $product = Product::factory()->create(['price' => 3500]);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'price' => 3500,
    ]);
});

test('it round-trips a money object through the database', function () {
    $original = Money::fromPounds(29.99);

    $product = Product::factory()->create(['price' => $original]);
    $product->refresh();

    expect($product->price)
        ->toBeInstanceOf(Money::class)
        ->amount->toBe(2999)
        ->currency->toBe('GBP');
});
