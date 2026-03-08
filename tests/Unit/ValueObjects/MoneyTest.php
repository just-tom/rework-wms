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

test('it formats as decimal string', function () {
    $money = new Money(1250);

    expect($money->format())->toBe('12.50 GBP');
});

test('it casts to string using format', function () {
    $money = new Money(1250);

    expect((string) $money)->toBe('12.50 GBP');
});
