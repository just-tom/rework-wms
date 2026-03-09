<?php

use App\ValueObjects\Money;

test('it constructs with amount and default currency', function () {
    $money = new Money(1250);

    expect($money->amount)->toBe(1250)
        ->and($money->currency)->toBe('GBP');
});

test('it creates from pounds', function () {
    $money = Money::fromPounds(12.50);

    expect($money->amount)->toBe(1250)
        ->and($money->currency)->toBe('GBP');
});

test('it rounds correctly when creating from pounds', function () {
    $money = Money::fromPounds(19.999);

    expect($money->amount)->toBe(2000);
});

test('it converts to pounds', function () {
    $money = new Money(1250);

    expect($money->toPounds())->toBe(12.5);
});

test('it formats with GBP symbol', function () {
    $money = new Money(1250);

    expect($money->format())->toBe('£12.50');
});

test('it casts to string using format', function () {
    $money = new Money(1250);

    expect((string) $money)->toBe('£12.50');
});

test('it formats with currency code for unknown currencies', function () {
    $money = new Money(1000, 'JPY');

    expect($money->format())->toBe('JPY 10.00');
});

test('it serializes to json with amount and formatted string', function () {
    $money = new Money(1250);

    expect($money->jsonSerialize())->toBe([
        'amount' => 1250,
        'currency' => 'GBP',
        'formatted' => '£12.50',
    ]);
});

test('it multiplies the amount', function () {
    $money = new Money(500);

    $result = $money->multiply(3);

    expect($result->amount)->toBe(1500)
        ->and($result->currency)->toBe('GBP');
});
