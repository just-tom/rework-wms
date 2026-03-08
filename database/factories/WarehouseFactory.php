<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Warehouse> */
final class WarehouseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'geo_location' => null,
            'address_1' => fake()->streetAddress(),
            'address_2' => null,
            'town' => fake()->city(),
            'county' => fake()->state(),
            'postcode' => fake()->postcode(),
            'state_code' => null,
            'country_code' => fake()->countryCode(),
        ];
    }
}
