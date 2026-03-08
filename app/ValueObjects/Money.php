<?php

declare(strict_types=1);

namespace App\ValueObjects;

final readonly class Money
{
    public function __construct(
        public int $amount,
        public string $currency = 'GBP',
    ) {}

    public function __toString(): string
    {
        return $this->format();
    }

    public static function fromPounds(float $pounds, string $currency = 'GBP'): self
    {
        return new self((int) round($pounds * 100), $currency);
    }

    public function toPounds(): float
    {
        return $this->amount / 100;
    }

    public function format(): string
    {
        return number_format($this->toPounds(), 2).' '.$this->currency;
    }
}
