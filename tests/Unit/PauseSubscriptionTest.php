<?php

use Stripe\StripeClient;
use Stripe\Subscription;
use Ghijk\DonationCheckout\Actions\PauseSubscription;

it('pauses a subscription with void behavior', function () {
    $mockSubscriptionsService = Mockery::mock();
    $expectedSubscription = Mockery::mock(Subscription::class);

    $mockSubscriptionsService->shouldReceive('update')
        ->once()
        ->with('sub_123', Mockery::on(function (array $args) {
            return $args['pause_collection']['behavior'] === 'void';
        }))
        ->andReturn($expectedSubscription);

    $mockClient = Mockery::mock(StripeClient::class);
    $mockClient->subscriptions = $mockSubscriptionsService;

    $action = new PauseSubscription($mockClient);
    $result = $action('sub_123');

    expect($result)->toBe($expectedSubscription);
});
