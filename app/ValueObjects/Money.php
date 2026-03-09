<?php

declare(strict_types=1);

namespace App\ValueObjects;

use JsonSerializable;

final readonly class Money implements JsonSerializable
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

    public function multiply(int $multiplier): self
    {
        return new self($this->amount * $multiplier, $this->currency);
    }

    public function format(): string
    {
        return match ($this->currency) {
            'GBP' => '£',
            default => $this->currency.' ',
        }.number_format($this->toPounds(), 2);
    }

    public function jsonSerialize(): array
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency,
            'formatted' => $this->format(),
        ];
    }
}
