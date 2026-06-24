<?php

use Stripe\Refund;
use Stripe\StripeClient;
use Ghijk\DonationCheckout\Actions\RefundPayment;

it('creates a full refund for a payment intent', function () {
    $mockRefundsService = Mockery::mock();
    $expectedRefund = Mockery::mock(Refund::class);

    $mockRefundsService->shouldReceive('create')
        ->once()
        ->with(Mockery::on(function (array $args) {
            return $args['payment_intent'] === 'pi_123';
        }))
        ->andReturn($expectedRefund);

    $mockClient = Mockery::mock(StripeClient::class);
    $mockClient->refunds = $mockRefundsService;

    $action = new RefundPayment($mockClient);
    $result = $action('pi_123');

    expect($result)->toBe($expectedRefund);
});
