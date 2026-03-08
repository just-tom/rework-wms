<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<WarehouseStock> */
final class WarehouseStockFactory extends Factory
{
    public function definition(): array
    {
        return [
            'warehouse_id' => Warehouse::factory(),
            'product_id' => Product::factory(),
            'quantity' => fake()->numberBetween(0, 1000),
            'threshold' => fake()->numberBetween(1, 50),
        ];
    }
}
