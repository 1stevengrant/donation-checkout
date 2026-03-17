<?php

use Stripe\StripeClient;
use Stripe\Checkout\Session;
use Ghijk\DonationCheckout\Actions\CreateRecurringDonation;

it('creates a subscription-mode checkout session with quantity-based pricing', function () {
    $mockSessionsService = Mockery::mock();
    $mockCheckout = Mockery::mock();
    $mockCheckout->sessions = $mockSessionsService;

    $expectedSession = Mockery::mock(Session::class);

    $mockSessionsService->shouldReceive('create')
        ->once()
        ->with(Mockery::on(function (array $args) {
            return $args['customer'] === 'cus_456'
                && $args['line_items'][0]['price'] === 'price_test_123'
                && $args['line_items'][0]['quantity'] === 10
                && $args['mode'] === 'subscription';
        }))
        ->andReturn($expectedSession);

    $mockClient = Mockery::mock(StripeClient::class);
    $mockClient->checkout = $mockCheckout;

    $action = new CreateRecurringDonation($mockClient);
    $result = $action('cus_456', 10);

    expect($result)->toBe($expectedSession);
});

it('uses amount directly as quantity', function () {
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

    $action = new CreateRecurringDonation($mockClient);
    $action('cus_789', 50);

    expect($capturedArgs['line_items'][0]['quantity'])->toBe(50);
});
