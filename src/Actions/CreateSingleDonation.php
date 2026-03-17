<?php

namespace Ghijk\DonationCheckout\Actions;

use Stripe\StripeClient;
use Stripe\Checkout\Session;

class CreateSingleDonation
{
    public function __construct(
        private readonly StripeClient $stripe
    ) {}

    public function __invoke(string $stripeCustomerId, int $amount): Session
    {
        return $this->stripe->checkout->sessions->create([
            'customer' => $stripeCustomerId,
            'submit_type' => 'donate',
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => config('donation-checkout.stripe_currency'),
                    'unit_amount' => $amount * 100,
                    'product_data' => [
                        'name' => config('donation-checkout.single_donation_product_name'),
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => config('donation-checkout.single_donation_success_url'),
            'cancel_url' => config('donation-checkout.single_donation_cancel_url'),
        ]);
    }
}
