<?php

namespace Ghijk\DonationCheckout\Actions;

use Stripe\StripeClient;
use Stripe\Checkout\Session;
use Ghijk\DonationCheckout\Support\Settings;

class CreateRecurringDonation
{
    public function __construct(
        private readonly StripeClient $stripe
    ) {}

    public function __invoke(string $stripeCustomerId, int $amount, array $metadata = []): Session
    {
        $params = [
            'customer' => $stripeCustomerId,
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price' => config('donation-checkout.stripe_price_plan_id'),
                'quantity' => $amount,
            ]],
            'mode' => 'subscription',
            'success_url' => $this->absoluteUrl(config('donation-checkout.recurring_donation_success_url')),
            'cancel_url' => $this->absoluteUrl(config('donation-checkout.recurring_donation_cancel_url')),
        ];

        if ($metadata) {
            $params['metadata'] = $metadata;
            $params['subscription_data'] = ['metadata' => $metadata];
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
