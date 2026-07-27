<?php

namespace Ghijk\DonationCheckout\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Ghijk\DonationCheckout\Http\Controllers\ThankYouController;
use Ghijk\DonationCheckout\Http\Middleware\VerifyStripeWebhook;
use Ghijk\DonationCheckout\Http\Controllers\MagicLinkController;
use Ghijk\DonationCheckout\Http\Controllers\DonorPortalController;
use Ghijk\DonationCheckout\Http\Controllers\StartDonationController;
use Ghijk\DonationCheckout\Http\Controllers\StripeWebhookController;
use Ghijk\DonationCheckout\Http\Controllers\DonorCancelSubscriptionController;

abstract class TestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app['view']->addNamespace('donation-checkout', __DIR__ . '/../resources/views');
    }

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
            'magic_link_expiry_hours' => 24,
            'donor_portal_enabled' => true,
            'donor_can_cancel_subscriptions' => true,
            'thank_you_route' => '/donation-checkout/thank-you',
            'collect_billing_address' => false,
            'custom_fields' => [],
            'stripe_webhook_secret' => 'whsec_test_secret',
        ]);
    }

    protected function defineRoutes($router): void
    {
        $router->post('/donation-checkout/start', StartDonationController::class);
        $router->get('/donation-checkout/thank-you', ThankYouController::class)->name('donation-checkout.thank-you');
        $router->post('/donation-checkout/magic-link', [MagicLinkController::class, 'store'])
            ->middleware(['throttle:5,1'])
            ->name('donation-checkout.magic-link.store');
        $router->get('/donation-checkout/magic-link/verify', [MagicLinkController::class, 'verify'])
            ->middleware(['signed'])
            ->name('donation-checkout.magic-link.verify');
        $router->get('/donation-checkout/portal', [DonorPortalController::class, 'index'])
            ->middleware(['auth'])
            ->name('donation-checkout.portal');
        $router->post('/donation-checkout/portal/subscriptions/{id}/cancel', DonorCancelSubscriptionController::class)
            ->middleware(['auth'])
            ->name('donation-checkout.portal.subscriptions.cancel');
        $router->post('/donation-checkout/webhook/stripe', StripeWebhookController::class)
            ->middleware(VerifyStripeWebhook::class)
            ->name('donation-checkout.webhook');
    }
}
