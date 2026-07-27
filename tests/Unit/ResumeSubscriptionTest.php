<?php

use Stripe\StripeClient;
use Stripe\Subscription;
use Ghijk\DonationCheckout\Actions\ResumeSubscription;

it('resumes a subscription by clearing pause_collection', function () {
    $mockSubscriptionsService = Mockery::mock();
    $expectedSubscription = Mockery::mock(Subscription::class);

    $mockSubscriptionsService->shouldReceive('update')
        ->once()
        ->with('sub_123', Mockery::on(function (array $args) {
            return $args['pause_collection'] === '';
        }))
        ->andReturn($expectedSubscription);

    $mockClient = Mockery::mock(StripeClient::class);
    $mockClient->subscriptions = $mockSubscriptionsService;

    $action = new ResumeSubscription($mockClient);
    $result = $action('sub_123');

    expect($result)->toBe($expectedSubscription);
});
