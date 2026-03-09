<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\WarehouseStock;
use Illuminate\Database\Seeder;

final class OrderSeeder extends Seeder
{
    public function run(): void
    {
        OrderItem::query()->delete();
        Order::query()->delete();

        $products = Product::whereHas('warehouseStocks')->get();

        Order::factory(25)->create()->each(function (Order $order) use ($products) {
            $itemCount = fake()->numberBetween(1, 5);
            $orderTotal = 0;

            $selectedProducts = $products->random($itemCount);

            foreach ($selectedProducts as $product) {
                $price = $product->getRawOriginal('price');
                $quantity = fake()->numberBetween(1, 5);
                $total = $price * $quantity;
                $orderTotal += $total;

                $warehouseStock = WarehouseStock::where('product_id', $product->id)->inRandomOrder()->first();

                OrderItem::factory()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouseStock?->warehouse_id,
                    'price' => $price,
                    'quantity' => $quantity,
                    'total' => $total,
                ]);
            }

            $order->update(['total' => $orderTotal]);
        });
    }
}
