<?php

declare(strict_types=1);

namespace App\Managers;

use App\Enums\OrderStatus;
use App\Models\Product;
use App\Models\Warehouse;

final readonly class StockManager
{
    public function __construct(
        private Product $product,
        private ?int $warehouseId = null,
    ) {}

    public static function for(Product $product): self
    {
        return new self($product);
    }

    public function in(Warehouse|int $warehouse): self
    {
        return new self(
            $this->product,
            $warehouse instanceof Warehouse ? $warehouse->id : $warehouse,
        );
    }

    public function allocatedToOrders(): int
    {
        $query = $this->product->orderItems()
            ->whereHas('order', fn ($query) => $query->where('order_status', OrderStatus::Placed));

        if ($this->warehouseId !== null) {
            $query->where('warehouse_id', $this->warehouseId);
        }

        return (int) $query->sum('quantity');
    }

    public function physicalQuantity(): int
    {
        return $this->warehouseQuantity() + $this->allocatedToOrders();
    }

    public function totalThreshold(): int
    {
        $query = $this->product->warehouseStocks();

        if ($this->warehouseId !== null) {
            $query->where('warehouse_id', $this->warehouseId);
        }

        return (int) $query->sum('threshold');
    }

    public function immediateDespatch(): int
    {
        return $this->warehouseQuantity() - $this->totalThreshold();
    }

    private function warehouseQuantity(): int
    {
        $query = $this->product->warehouseStocks();

        if ($this->warehouseId !== null) {
            $query->where('warehouse_id', $this->warehouseId);
        }

        return (int) $query->sum('quantity');
    }
}
