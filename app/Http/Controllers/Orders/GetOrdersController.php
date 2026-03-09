<?php

declare(strict_types=1);

namespace App\Http\Controllers\Orders;

use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class GetOrdersController
{
    public function __invoke(Request $request): Response
    {
        $orders = Order::with('items.product', 'items.warehouse')
            ->latest()
            ->paginate(20);

        return Inertia::render('Orders/Index', [
            'orders' => OrderResource::collection($orders),
        ]);
    }
}
