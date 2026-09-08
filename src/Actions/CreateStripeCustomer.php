<?php

namespace Ghijk\DonationCheckout\Actions;

use Stripe\Customer;
use Stripe\StripeClient;

class CreateStripeCustomer
{
    public function __construct(
        private readonly StripeClient $stripe
    ) {}

    public function __invoke(string $email, string $name): Customer
    {
        $existing = $this->stripe->customers->all([
            'email' => $email,
            'limit' => 1,
        ])->first();

        if ($existing instanceof Customer) {
            return $existing;
        }

        return $this->stripe->customers->create([
            'email' => $email,
            'name' => $name,
        ]);
    }
}
