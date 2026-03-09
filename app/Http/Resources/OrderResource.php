<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class OrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'status' => [
                'label' => $this->order_status->getLabel(),
                'value' => $this->order_status->value,
                'classes' => $this->order_status->badgeClasses(),
            ],
            'total' => [
                'amount' => $this->total->amount,
                'formatted' => $this->total->format(),
            ],
            'productTitle' => $this->items->first()?->product->title,
            'quantity' => $this->items->first()?->quantity,
            'warehouseName' => $this->items->first()?->warehouse?->name,
            'createdAt' => $this->created_at->toISOString(),
            'canDispatch' => $this->order_status->canTransitionTo(OrderStatus::Dispatched),
            'canCancel' => $this->order_status->canTransitionTo(OrderStatus::Cancelled),
        ];
    }
}
