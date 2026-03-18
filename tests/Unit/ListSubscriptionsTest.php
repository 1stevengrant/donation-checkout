<?php

use Stripe\Collection;
use Stripe\StripeClient;
use Ghijk\DonationCheckout\Actions\ListSubscriptions;

it('lists subscriptions for a customer', function () {
    $mockSubscriptionsService = Mockery::mock();
    $expectedCollection = Mockery::mock(Collection::class);

    $mockSubscriptionsService->shouldReceive('all')
        ->once()
        ->with(Mockery::on(function (array $args) {
            return $args['customer'] === 'cus_123'
                && $args['limit'] === 10
                && ! isset($args['starting_after']);
        }))
        ->andReturn($expectedCollection);

    $mockClient = Mockery::mock(StripeClient::class);
    $mockClient->subscriptions = $mockSubscriptionsService;

    $action = new ListSubscriptions($mockClient);
    $result = $action('cus_123');

    expect($result)->toBe($expectedCollection);
});

it('supports pagination with starting_after', function () {
    $mockSubscriptionsService = Mockery::mock();
    $expectedCollection = Mockery::mock(Collection::class);

    $mockSubscriptionsService->shouldReceive('all')
        ->once()
        ->with(Mockery::on(function (array $args) {
            return $args['customer'] === 'cus_123'
                && $args['limit'] === 50
                && $args['starting_after'] === 'sub_last';
        }))
        ->andReturn($expectedCollection);

    $mockClient = Mockery::mock(StripeClient::class);
    $mockClient->subscriptions = $mockSubscriptionsService;

    $action = new ListSubscriptions($mockClient);
    $result = $action('cus_123', 50, 'sub_last');

    expect($result)->toBe($expectedCollection);
});
