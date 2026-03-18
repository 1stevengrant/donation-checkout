<?php

namespace Ghijk\DonationCheckout\Actions;

use Stripe\Collection;
use Stripe\StripeClient;

class ListDonations
{
    public function __construct(
        private readonly StripeClient $stripe
    ) {}

    public function __invoke(string $customerId, int $limit = 10, ?string $startingAfter = null): Collection
    {
        $params = [
            'customer' => $customerId,
            'limit' => $limit,
            'expand' => ['data.latest_charge'],
        ];

        if ($startingAfter) {
            $params['starting_after'] = $startingAfter;
        }

        return $this->stripe->paymentIntents->all($params);
    }
}
