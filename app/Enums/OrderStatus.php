<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderStatus: string
{
    case Placed = 'placed';
    case Dispatched = 'dispatched';
    case Cancelled = 'cancelled';
}
