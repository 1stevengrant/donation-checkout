<?php

namespace Ghijk\DonationCheckout\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Ghijk\DonationCheckout\Http\Controllers\StartDonationController;

abstract class TestCase extends OrchestraTestCase
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('donation-checkout', [
            'stripe_currency' => 'gbp',
            'stripe_price_plan_id' => 'price_test_123',
            'stripe_secret_key' => 'sk_test_fake',
            'stripe_publishable_key' => 'pk_test_fake',
            'single_donation_product_name' => 'Test Donation',
            'single_donation_success_url' => 'https://example.com/success',
            'single_donation_cancel_url' => 'https://example.com/cancel',
            'single_donation_success_message' => 'Thank you!',
            'recurring_donation_success_url' => 'https://example.com/success',
            'recurring_donation_cancel_url' => 'https://example.com/cancel',
            'recurring_donation_success_message' => 'Thank you!',
            'default_amount' => 10,
            'default_frequency' => 'recurring',
        ]);
    }

    protected function defineRoutes($router): void
    {
        $router->post('/donation-checkout/start', StartDonationController::class);
    }
}
