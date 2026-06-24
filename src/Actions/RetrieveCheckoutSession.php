<?php

namespace Ghijk\DonationCheckout\Actions;

use Stripe\StripeClient;
use Stripe\Checkout\Session;

class RetrieveCheckoutSession
{
    public function __construct(
        private readonly StripeClient $stripe
    ) {}

    public function __invoke(string $sessionId): Session
    {
        return $this->stripe->checkout->sessions->retrieve($sessionId, [
            'expand' => ['line_items'],
        ]);
    }
}
