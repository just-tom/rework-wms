<?php

declare(strict_types=1);

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use InvalidArgumentException;

final class DispatchOrderAction
{
    public function execute(Order $order): Order
    {
        if (! $order->order_status->canTransitionTo(OrderStatus::Dispatched)) {
            throw new InvalidArgumentException(
                "Cannot dispatch order with status '{$order->order_status->value}'."
            );
        }

        $order->update(['order_status' => OrderStatus::Dispatched]);

        return $order;
    }
}
