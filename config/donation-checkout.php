<?php

return [
    'stripe_currency' => 'gbp',
    'stripe_price_plan_id' => '', // e.g 'price_0MACBYAGG6RS7KP5c1fNa6v9',
    'stripe_secret_key' => env('STRIPE_SECRET_KEY', ''),
    'stripe_publishable_key' => env('STRIPE_PUBLISHABLE_KEY', ''),
    'stripe_webhook_secret' => env('STRIPE_WEBHOOK_SECRET', ''),

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

    // Magic link expiry (hours)
    'magic_link_expiry_hours' => 24,

    // Donor portal
    'donor_portal_enabled' => true,
    'donor_can_cancel_subscriptions' => true,

    // Thank you page route
    'thank_you_route' => '/donation-checkout/thank-you',

    // Billing address collection (needed for Gift Aid / HMRC compliance)
    // When true, sets billing_address_collection: 'required' on Stripe session
    'collect_billing_address' => false,

    // Custom metadata fields to collect and pass to Stripe session metadata
    // Each key is the field name, value is the config for the form field
    'custom_fields' => [
        // 'gift_aid' => ['type' => 'checkbox', 'label' => 'Boost your donation by 25% with Gift Aid'],
        // 'message' => ['type' => 'text', 'label' => 'Leave a message'],
        // 'newsletter' => ['type' => 'checkbox', 'label' => 'Subscribe to our newsletter'],
    ],
];
