<?php

use Stripe\StripeClient;
use Stripe\Subscription;
use Ghijk\DonationCheckout\Actions\CancelSubscription;

it('cancels a subscription', function () {
    $mockSubscriptionsService = Mockery::mock();
    $expectedSubscription = Mockery::mock(Subscription::class);

    $mockSubscriptionsService->shouldReceive('cancel')
        ->once()
        ->with('sub_123')
        ->andReturn($expectedSubscription);

    $mockClient = Mockery::mock(StripeClient::class);
    $mockClient->subscriptions = $mockSubscriptionsService;

    $action = new CancelSubscription($mockClient);
    $result = $action('sub_123');

    expect($result)->toBe($expectedSubscription);
});
