<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Order> */
final class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_status' => fake()->randomElement(OrderStatus::cases()),
            'total' => 0,
        ];
    }
}
