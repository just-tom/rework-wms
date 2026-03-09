<?php

declare(strict_types=1);

namespace App\Managers;

use App\Enums\OrderStatus;
use App\Models\Product;

final readonly class StockManager
{
    public function __construct(
        private Product $product,
    ) {}

    public static function for(Product $product): self
    {
        return new self($product);
    }

    public function allocatedToOrders(): int
    {
        return (int) $this->product->orderItems()
            ->whereHas('order', fn ($query) => $query->where('order_status', OrderStatus::Placed))
            ->sum('quantity');
    }

    public function physicalQuantity(): int
    {
        return $this->warehouseQuantity() + $this->allocatedToOrders();
    }

    public function totalThreshold(): int
    {
        return (int) $this->product->warehouseStocks()->sum('threshold');
    }

    public function immediateDespatch(): int
    {
        return $this->warehouseQuantity() - $this->totalThreshold();
    }

    private function warehouseQuantity(): int
    {
        return (int) $this->product->warehouseStocks()->sum('quantity');
    }
}
