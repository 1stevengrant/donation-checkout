<?php

namespace Ghijk\DonationCheckout\Events;

use Illuminate\Foundation\Events\Dispatchable;

class SubscriptionUpdated
{
    use Dispatchable;

    public function __construct(
        public readonly string $stripeCustomerId,
        public readonly string $subscriptionId,
        public readonly array $data
    ) {}
}
