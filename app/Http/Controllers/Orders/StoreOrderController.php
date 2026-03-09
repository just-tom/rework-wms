<?php

declare(strict_types=1);

namespace App\Http\Controllers\Orders;

use App\Actions\Orders\PlaceOrderAction;
use App\Data\StoreOrderData;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\OutOfStockException;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

final class StoreOrderController
{
    public function __invoke(StoreOrderData $data, PlaceOrderAction $action): Response
    {
        try {
            $action->execute($data);
        } catch (OutOfStockException|InsufficientStockException $e) {
            throw ValidationException::withMessages([
                'quantity' => $e->toValidationMessage(),
            ]);
        }

        return Inertia::flash('success', 'Order placed successfully!')->back();
    }
}
