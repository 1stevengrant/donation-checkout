<?php

namespace Ghijk\DonationCheckout\Actions;

use Stripe\StripeClient;
use Stripe\Checkout\Session;

class CreateRecurringDonation
{
    public function __construct(
        private readonly StripeClient $stripe
    ) {}

    public function __invoke(string $stripeCustomerId, int $amount): Session
    {
        return $this->stripe->checkout->sessions->create([
            'customer' => $stripeCustomerId,
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price' => config('donation-checkout.stripe_price_plan_id'),
                'quantity' => $amount,
            ]],
            'mode' => 'subscription',
            'success_url' => config('donation-checkout.recurring_donation_success_url'),
            'cancel_url' => config('donation-checkout.recurring_donation_cancel_url'),
        ]);
    }
}
