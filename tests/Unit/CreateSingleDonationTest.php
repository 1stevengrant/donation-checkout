<?php

use Stripe\StripeClient;
use Stripe\Checkout\Session;
use Ghijk\DonationCheckout\Actions\CreateSingleDonation;

it('creates a payment-mode checkout session', function () {
    $mockSessionsService = Mockery::mock();
    $mockCheckout = Mockery::mock();
    $mockCheckout->sessions = $mockSessionsService;

    $expectedSession = Mockery::mock(Session::class);

    $mockSessionsService->shouldReceive('create')
        ->once()
        ->with(Mockery::on(function (array $args) {
            return $args['customer'] === 'cus_123'
                && $args['line_items'][0]['price_data']['unit_amount'] === 2500
                && $args['line_items'][0]['price_data']['currency'] === 'gbp'
                && $args['line_items'][0]['quantity'] === 1
                && $args['mode'] === 'payment'
                && $args['submit_type'] === 'donate';
        }))
        ->andReturn($expectedSession);

    $mockClient = Mockery::mock(StripeClient::class);
    $mockClient->checkout = $mockCheckout;

    $action = new CreateSingleDonation($mockClient);
    $result = $action('cus_123', 25);

    expect($result)->toBe($expectedSession);
});

it('converts amount to minor currency unit by multiplying by 100', function () {
    $mockSessionsService = Mockery::mock();
    $mockCheckout = Mockery::mock();
    $mockCheckout->sessions = $mockSessionsService;

    $capturedArgs = null;

    $mockSessionsService->shouldReceive('create')
        ->once()
        ->with(Mockery::on(function (array $args) use (&$capturedArgs) {
            $capturedArgs = $args;

            return true;
        }))
        ->andReturn(Mockery::mock(Session::class));

    $mockClient = Mockery::mock(StripeClient::class);
    $mockClient->checkout = $mockCheckout;

    $action = new CreateSingleDonation($mockClient);
    $action('cus_789', 1);

    expect($capturedArgs['line_items'][0]['price_data']['unit_amount'])->toBe(100);
});
