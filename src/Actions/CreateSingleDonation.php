<?php

namespace Ghijk\DonationCheckout\Actions;

use Stripe\StripeClient;
use Stripe\Checkout\Session;
use Ghijk\DonationCheckout\Support\Settings;

class CreateSingleDonation
{
    public function __construct(
        private readonly StripeClient $stripe
    ) {}

    public function __invoke(string $stripeCustomerId, int $amount, array $metadata = []): Session
    {
        $params = [
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
            'success_url' => $this->absoluteUrl(config('donation-checkout.single_donation_success_url')),
            'cancel_url' => $this->absoluteUrl(config('donation-checkout.single_donation_cancel_url')),
        ];

        if ($metadata) {
            $params['metadata'] = $metadata;
        }

        if (Settings::collectBillingAddress()) {
            $params['billing_address_collection'] = 'required';
        }

        return $this->stripe->checkout->sessions->create($params);
    }

    private function absoluteUrl(string $value): string
    {
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        return url($value);
    }
}
