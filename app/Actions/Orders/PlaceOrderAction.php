<?php

declare(strict_types=1);

namespace App\Actions\Orders;

use App\Data\StoreOrderData;
use App\Enums\OrderStatus;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\OutOfStockException;
use App\Models\Order;
use App\Models\Product;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;

final class PlaceOrderAction
{
    public function execute(StoreOrderData $data): Order
    {
        return DB::transaction(function () use ($data) {
            $product = Product::where('uuid', $data->productUuid)->firstOrFail();

            $warehouseStock = WarehouseStock::where('product_id', $product->id)
                ->sellable()
                ->orderByRaw('(quantity - threshold) DESC')
                ->lockForUpdate()
                ->first();

            if (! $warehouseStock) {
                throw new OutOfStockException;
            }

            $sellable = $warehouseStock->quantity - $warehouseStock->threshold;

            if ($sellable < $data->quantity) {
                throw new InsufficientStockException(
                    available: $sellable,
                );
            }

            $warehouseStock->decrement('quantity', $data->quantity);

            $itemTotal = $product->price->multiply($data->quantity);

            $order = Order::create([
                'order_status' => OrderStatus::Placed,
                'total' => $itemTotal,
            ]);

            $order->items()->create([
                'product_id' => $product->id,
                'price' => $product->price,
                'quantity' => $data->quantity,
                'total' => $itemTotal,
            ]);

            return $order;
        });
    }
}
