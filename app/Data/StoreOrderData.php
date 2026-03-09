<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

final class StoreOrderData extends Data
{
    public function __construct(
        #[Required, Exists('products', 'uuid')]
        public string $productUuid,
        #[Required, IntegerType, Min(1)]
        public int $quantity,
    ) {}
}
