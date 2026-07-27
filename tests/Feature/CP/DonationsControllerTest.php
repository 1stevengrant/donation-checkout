<?php

use Stripe\Refund;
use Stripe\Subscription;
use Ghijk\DonationCheckout\Actions\RefundPayment;
use Ghijk\DonationCheckout\Actions\PauseSubscription;
use Ghijk\DonationCheckout\Actions\CancelSubscription;
use Ghijk\DonationCheckout\Actions\ResumeSubscription;

it('cancels a subscription via the action', function () {
    $mockSubscription = Mockery::mock(Subscription::class);

    $this->mock(CancelSubscription::class, function ($mock) use ($mockSubscription) {
        $mock->shouldReceive('__invoke')
            ->once()
            ->with('sub_test_123')
            ->andReturn($mockSubscription);
    });

    $action = app(CancelSubscription::class);
    $result = $action('sub_test_123');

    expect($result)->toBe($mockSubscription);
});

it('pauses a subscription via the action', function () {
    $mockSubscription = Mockery::mock(Subscription::class);

    $this->mock(PauseSubscription::class, function ($mock) use ($mockSubscription) {
        $mock->shouldReceive('__invoke')
            ->once()
            ->with('sub_test_123')
            ->andReturn($mockSubscription);
    });

    $action = app(PauseSubscription::class);
    $result = $action('sub_test_123');

    expect($result)->toBe($mockSubscription);
});

it('resumes a subscription via the action', function () {
    $mockSubscription = Mockery::mock(Subscription::class);

    $this->mock(ResumeSubscription::class, function ($mock) use ($mockSubscription) {
        $mock->shouldReceive('__invoke')
            ->once()
            ->with('sub_test_123')
            ->andReturn($mockSubscription);
    });

    $action = app(ResumeSubscription::class);
    $result = $action('sub_test_123');

    expect($result)->toBe($mockSubscription);
});

it('refunds a payment via the action', function () {
    $mockRefund = Mockery::mock(Refund::class);

    $this->mock(RefundPayment::class, function ($mock) use ($mockRefund) {
        $mock->shouldReceive('__invoke')
            ->once()
            ->with('pi_test_123')
            ->andReturn($mockRefund);
    });

    $action = app(RefundPayment::class);
    $result = $action('pi_test_123');

    expect($result)->toBe($mockRefund);
});
