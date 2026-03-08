<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->json('geo_location')->nullable();
            $table->string('address_1');
            $table->string('address_2')->nullable();
            $table->string('town');
            $table->string('county')->nullable();
            $table->string('postcode');
            $table->string('state_code')->nullable();
            $table->string('country_code', 2);
            $table->timestamps();
        });
    }
};
