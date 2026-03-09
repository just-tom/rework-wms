<?php

declare(strict_types=1);

namespace App\Http\Controllers\Stock;

use App\Http\Resources\StockOverviewResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class GetStockOverviewController
{
    public function __invoke(Request $request): Response
    {
        $products = Product::with('warehouseStocks.warehouse')
            ->orderBy('title')
            ->paginate(25);

        return Inertia::render('Stock/Index', [
            'products' => StockOverviewResource::collection($products),
        ]);
    }
}
