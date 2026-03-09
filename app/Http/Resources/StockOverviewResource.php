<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Managers\StockManager;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class StockOverviewResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $manager = StockManager::for($this->resource);

        return [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'allocatedToOrders' => $manager->allocatedToOrders(),
            'physicalQuantity' => $manager->physicalQuantity(),
            'totalThreshold' => $manager->totalThreshold(),
            'immediateDespatch' => $manager->immediateDespatch(),
            'warehouses' => $this->warehouseStocks->map(fn ($stock) => [
                'name' => $stock->warehouse->name,
                'quantity' => $stock->quantity,
                'threshold' => $stock->threshold,
            ]),
        ];
    }
}
