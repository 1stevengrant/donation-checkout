<?php

return [
    'stripe_currency' => 'gbp',
    'stripe_price_plan_id' => '', // e.g 'price_0MACBYAGG6RS7KP5c1fNa6v9',
    'stripe_secret_key' => env('STRIPE_SECRET_KEY', ''),
    'stripe_publishable_key' => env('STRIPE_PUBLISHABLE_KEY', ''),

    'single_donation_product_name' => 'Donation',
    'single_donation_success_message' => 'Thank you for your donation!',
    'single_donation_success_url' => '', // e.g url('thanks?session_id={CHECKOUT_SESSION_ID}'),
    'single_donation_cancel_url' => '', // e.g url('?cancel=true'),

    'recurring_donation_success_message' => 'Thank you for your donation!',
    'recurring_donation_success_url' => '', // e.g url('thanks?session_id={CHECKOUT_SESSION_ID}'),
    'recurring_donation_cancel_url' => '', // e.g url('?cancel=true'),

    // Form defaults (used by donation:scripts tag)
    'default_amount' => 10,
    'default_frequency' => 'recurring', // 'single' or 'recurring'

    /*
     * Create a Statamic user for every donor, keyed on their email address, so
     * their Stripe customer can be reused on future donations.
     *
     * The donation endpoint is public, so leaving this enabled means anonymous
     * visitors can create user records. Set it to false if your site does not
     * need donor accounts; donors are then matched on their Stripe customer
     * record instead and no Statamic users are written.
     */
    'create_users' => true,

    /*
     * Per-IP rate limits for POST /donation-checkout/start. This endpoint calls
     * the Stripe API and (when enabled above) writes user records, so keep the
     * limits only as high as a genuine donor needs.
     */
    'rate_limit_per_minute' => 5,
    'rate_limit_per_day' => 50,
];
