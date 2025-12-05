<?php

namespace Ghijk\DonationCheckout\Services;

use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\Stripe;

class PaymentService
{
    public function __construct()
    {
        Stripe::setApiKey(config('donation-checkout.stripe_secret_key'));
    }

    public function getCustomer(string $id)
    {
        return Customer::retrieve($id);
    }

    public function createCustomer(
        string $email,
        string $name
    ) {
        return Customer::create([
            'email' => $email,
            'name' => $name,
        ]);
    }

    public function singleDonation($user, int $amount)
    {
        return Session::create([
            'customer' => $user->stripe_customer_id,
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

    public function recurringDonation($user, int $amount)
    {
        return Session::create([
            'customer' => $user->stripe_customer_id,
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
