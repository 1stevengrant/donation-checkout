<?php

namespace Ghijk\DonationCheckout\Actions;

use Stripe\StripeClient;
use Stripe\Subscription;

class CancelSubscription
{
    public function __construct(
        private readonly StripeClient $stripe
    ) {}

    public function __invoke(string $subscriptionId): Subscription
    {
        return $this->stripe->subscriptions->cancel($subscriptionId);
    }
}
