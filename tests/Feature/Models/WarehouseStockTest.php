<?php

declare(strict_types=1);

use App\Models\WarehouseStock;

test('sellable scope includes stock where quantity exceeds threshold', function () {
    $stock = WarehouseStock::factory()->create([
        'quantity' => 10,
        'threshold' => 5,
    ]);

    $results = WarehouseStock::sellable()->pluck('id')->all();

    expect($results)->toBe([$stock->id]);
});

test('sellable scope excludes stock where quantity equals threshold', function () {
    WarehouseStock::factory()->create([
        'quantity' => 5,
        'threshold' => 5,
    ]);

    $results = WarehouseStock::sellable()->pluck('id')->all();

    expect($results)->toBeEmpty();
});

test('sellable scope excludes stock where quantity is below threshold', function () {
    WarehouseStock::factory()->create([
        'quantity' => 3,
        'threshold' => 10,
    ]);

    $results = WarehouseStock::sellable()->pluck('id')->all();

    expect($results)->toBeEmpty();
});

test('sellable scope excludes stock with zero quantity', function () {
    WarehouseStock::factory()->create([
        'quantity' => 0,
        'threshold' => 0,
    ]);

    $results = WarehouseStock::sellable()->pluck('id')->all();

    expect($results)->toBeEmpty();
});
