<?php

namespace Ghijk\DonationCheckout\Actions;

use Stripe\Collection;
use Stripe\StripeClient;

class ListSubscriptions
{
    public function __construct(
        private readonly StripeClient $stripe
    ) {}

    public function __invoke(string $customerId, int $limit = 10, ?string $startingAfter = null): Collection
    {
        $params = [
            'customer' => $customerId,
            'limit' => $limit,
        ];

        if ($startingAfter) {
            $params['starting_after'] = $startingAfter;
        }

        return $this->stripe->subscriptions->all($params);
    }
}
