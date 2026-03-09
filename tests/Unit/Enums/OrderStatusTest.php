<?php

declare(strict_types=1);

use App\Enums\OrderStatus;

test('placed can transition to dispatched', function () {
    expect(OrderStatus::Placed->canTransitionTo(OrderStatus::Dispatched))->toBeTrue();
});

test('placed can transition to cancelled', function () {
    expect(OrderStatus::Placed->canTransitionTo(OrderStatus::Cancelled))->toBeTrue();
});

test('placed cannot transition to placed', function () {
    expect(OrderStatus::Placed->canTransitionTo(OrderStatus::Placed))->toBeFalse();
});

test('dispatched can transition to cancelled', function () {
    expect(OrderStatus::Dispatched->canTransitionTo(OrderStatus::Cancelled))->toBeTrue();
});

test('dispatched cannot transition to placed', function () {
    expect(OrderStatus::Dispatched->canTransitionTo(OrderStatus::Placed))->toBeFalse();
});

test('dispatched cannot transition to dispatched', function () {
    expect(OrderStatus::Dispatched->canTransitionTo(OrderStatus::Dispatched))->toBeFalse();
});

test('cancelled cannot transition to any status', function () {
    expect(OrderStatus::Cancelled->canTransitionTo(OrderStatus::Placed))->toBeFalse();
    expect(OrderStatus::Cancelled->canTransitionTo(OrderStatus::Dispatched))->toBeFalse();
    expect(OrderStatus::Cancelled->canTransitionTo(OrderStatus::Cancelled))->toBeFalse();
});

test('badge classes returns correct pastel colors', function () {
    expect(OrderStatus::Placed->badgeClasses())->toContain('bg-blue-50');
    expect(OrderStatus::Dispatched->badgeClasses())->toContain('bg-green-50');
    expect(OrderStatus::Cancelled->badgeClasses())->toContain('bg-red-50');
});
