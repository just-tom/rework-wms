<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

final class Warehouse extends Model
{
    use HasFactory, HasSlug, HasUuid;

    protected function casts(): array
    {
        return [
            'geo_location' => 'array',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function warehouseStocks(): HasMany
    {
        return $this->hasMany(WarehouseStock::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'warehouse_stock')
            ->withPivot('quantity', 'threshold')
            ->withTimestamps();
    }
}
