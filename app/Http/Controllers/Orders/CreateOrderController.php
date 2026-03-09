<?php

declare(strict_types=1);

namespace App\Http\Controllers\Orders;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class CreateOrderController
{
    public function __invoke(Request $request): Response
    {
        return Inertia::render('Orders/PlaceOrder', [
            'products' => ProductResource::collection(
                Product::select('uuid', 'title', 'price')
                    ->inStock()
                    ->orderBy('title')
                    ->get()
            ),
        ]);
    }
}
