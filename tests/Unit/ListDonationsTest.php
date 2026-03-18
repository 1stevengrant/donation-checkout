<?php

use Stripe\Collection;
use Stripe\StripeClient;
use Ghijk\DonationCheckout\Actions\ListDonations;

it('lists payment intents for a customer', function () {
    $mockPaymentIntentsService = Mockery::mock();
    $expectedCollection = Mockery::mock(Collection::class);

    $mockPaymentIntentsService->shouldReceive('all')
        ->once()
        ->with(Mockery::on(function (array $args) {
            return $args['customer'] === 'cus_123'
                && $args['limit'] === 10
                && ! isset($args['starting_after']);
        }))
        ->andReturn($expectedCollection);

    $mockClient = Mockery::mock(StripeClient::class);
    $mockClient->paymentIntents = $mockPaymentIntentsService;

    $action = new ListDonations($mockClient);
    $result = $action('cus_123');

    expect($result)->toBe($expectedCollection);
});

it('supports pagination with starting_after', function () {
    $mockPaymentIntentsService = Mockery::mock();
    $expectedCollection = Mockery::mock(Collection::class);

    $mockPaymentIntentsService->shouldReceive('all')
        ->once()
        ->with(Mockery::on(function (array $args) {
            return $args['customer'] === 'cus_123'
                && $args['limit'] === 25
                && $args['starting_after'] === 'pi_last';
        }))
        ->andReturn($expectedCollection);

    $mockClient = Mockery::mock(StripeClient::class);
    $mockClient->paymentIntents = $mockPaymentIntentsService;

    $action = new ListDonations($mockClient);
    $result = $action('cus_123', 25, 'pi_last');

    expect($result)->toBe($expectedCollection);
});
