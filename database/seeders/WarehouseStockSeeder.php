<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Database\Seeder;

final class WarehouseStockSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = Warehouse::all();
        $products = Product::all();

        foreach ($warehouses as $warehouse) {
            $stockProducts = $products->random(fake()->numberBetween(5, 15));

            foreach ($stockProducts as $product) {
                WarehouseStock::factory()->create([
                    'warehouse_id' => $warehouse->id,
                    'product_id' => $product->id,
                ]);
            }
        }
    }
}
