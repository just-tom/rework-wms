<?php

declare(strict_types=1);

namespace App\Http\Controllers\Orders;

use App\Actions\Orders\DispatchOrderAction;
use App\Models\Order;
use Inertia\Inertia;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

final class DispatchOrderController
{
    public function __invoke(Order $order, DispatchOrderAction $action): Response
    {
        try {
            $action->execute($order);
        } catch (InvalidArgumentException) {
            abort(409);
        }
        return Inertia::flash('success', 'Order dispatched successfully.')->back();
    }
}
