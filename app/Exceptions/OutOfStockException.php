<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class OutOfStockException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('This product is currently out of stock.');
    }

    public function toValidationMessage(): string
    {
        return $this->getMessage();
    }
}
