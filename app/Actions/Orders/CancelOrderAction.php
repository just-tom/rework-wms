<?php

declare(strict_types=1);

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class CancelOrderAction
{
    public function execute(Order $order): Order
    {
        if (! $order->order_status->canTransitionTo(OrderStatus::Cancelled)) {
            throw new InvalidArgumentException(
                "Cannot cancel order with status '{$order->order_status->value}'."
            );
        }

        return DB::transaction(function () use ($order) {
            $order->update(['order_status' => OrderStatus::Cancelled]);

            $order->load('items');

            foreach ($order->items as $item) {
                WarehouseStock::where('product_id', $item->product_id)
                    ->where('warehouse_id', $item->warehouse_id)
                    ->increment('quantity', $item->quantity);
            }

            return $order;
        });
    }
}
