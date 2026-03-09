<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderStatus: string
{
    case Placed = 'placed';
    case Dispatched = 'dispatched';
    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::Placed => 'Placed',
            self::Dispatched => 'Dispatched',
            self::Cancelled => 'Cancelled',
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return match ($this) {
            self::Placed => in_array($status, [self::Dispatched, self::Cancelled]),
            self::Dispatched => $status === self::Cancelled,
            self::Cancelled => false,
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Placed => 'bg-blue-50 border-blue-300 text-blue-700',
            self::Dispatched => 'bg-green-50 border-green-300 text-green-700',
            self::Cancelled => 'bg-red-50 border-red-300 text-red-700',
        };
    }
}
