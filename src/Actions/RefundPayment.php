<?php

namespace Ghijk\DonationCheckout\Actions;

use Stripe\Refund;
use Stripe\StripeClient;

class RefundPayment
{
    public function __construct(
        private readonly StripeClient $stripe
    ) {}

    public function __invoke(string $paymentIntentId): Refund
    {
        return $this->stripe->refunds->create([
            'payment_intent' => $paymentIntentId,
        ]);
    }
}
