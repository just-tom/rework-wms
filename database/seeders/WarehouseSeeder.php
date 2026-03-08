<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

final class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        Warehouse::factory(2)->create();
    }
}
