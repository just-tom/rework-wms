<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<OrderItem> */
final class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        $price = fake()->numberBetween(100, 50000);
        $quantity = fake()->numberBetween(1, 10);

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'price' => $price,
            'quantity' => $quantity,
            'total' => $price * $quantity,
        ];
    }
}
