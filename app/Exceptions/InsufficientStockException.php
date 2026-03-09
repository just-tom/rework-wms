<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class InsufficientStockException extends RuntimeException
{
    public function __construct(
        public readonly int $available,
    ) {
        parent::__construct($this->toValidationMessage());
    }

    public function toValidationMessage(): string
    {
        return "Only {$this->available} units available.";
    }
}
