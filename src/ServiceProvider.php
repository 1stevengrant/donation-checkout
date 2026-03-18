<?php

namespace Ghijk\DonationCheckout;

use Statamic\Statamic;
use Stripe\StripeClient;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Permission;
use Illuminate\Support\Facades\Route;
use Ghijk\DonationCheckout\Tags\Donation;
use Statamic\Providers\AddonServiceProvider;
use Ghijk\DonationCheckout\Widgets\DonationStatsWidget;
use Ghijk\DonationCheckout\Http\Middleware\VerifyStripeWebhook;
use Ghijk\DonationCheckout\Http\Controllers\StripeWebhookController;
use Ghijk\DonationCheckout\Console\Commands\SetupStripeWebhookCommand;

class ServiceProvider extends AddonServiceProvider
{
    protected $publishAfterInstall = true;

    protected $tags = [
        Donation::class,
    ];

    protected $routes = [
        'web' => __DIR__ . '/../routes/web.php',
        'cp' => __DIR__ . '/../routes/cp.php',
    ];

    protected $widgets = [
        DonationStatsWidget::class,
    ];

    protected $commands = [
        SetupStripeWebhookCommand::class,
    ];

    protected $vite = [
        'input' => ['resources/js/addon.js'],
        'publicDirectory' => 'resources/dist',
    ];

    #[\Override]
    public function register(): void
    {
        parent::register();

        $this->mergeConfigFrom(
            __DIR__ . '/../config/donation-checkout.php', 'donation-checkout'
        );

        $this->app->singleton(StripeClient::class, fn (): StripeClient => new StripeClient(
            config('donation-checkout.stripe_secret_key')
        ));
    }

    #[\Override]
    public function bootAddon(): void
    {
        Statamic::afterInstalled(function ($command): void {
            $command->call('vendor:publish', ['--tag' => 'donation-checkout-config']);
        });

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'donation-checkout');

        $this->publishes([
            __DIR__ . '/../config/donation-checkout.php' => config_path('donation-checkout.php'),
        ], 'donation-checkout-config');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/donation-checkout'),
        ], 'donation-checkout-views');

        $this->publishes([
            __DIR__ . '/../resources/blueprints/globals/donation_messages.yaml' => resource_path('blueprints/globals/donation_messages.yaml'),
            __DIR__ . '/../resources/content/globals/donation_messages.yaml' => base_path('content/globals/donation_messages.yaml'),
            __DIR__ . '/../resources/blueprints/globals/donation_emails.yaml' => resource_path('blueprints/globals/donation_emails.yaml'),
            __DIR__ . '/../resources/content/globals/donation_emails.yaml' => base_path('content/globals/donation_emails.yaml'),
        ], 'donation-checkout-global-set');

        $this->registerWebhookRoute();
        $this->registerNavigation();
        $this->registerPermissions();
        $this->installGlobalSet();
    }

    private function registerWebhookRoute(): void
    {
        Route::post('donation-checkout/webhook/stripe', StripeWebhookController::class)
            ->middleware(VerifyStripeWebhook::class)
            ->name('donation-checkout.webhook');
    }

    private function registerNavigation(): void
    {
        Nav::extend(function ($nav): void {
            $nav->tools('Donations')
                ->route('donation-checkout.index')
                ->icon('gift-present-surprise')
                ->can('view donations');
        });
    }

    private function registerPermissions(): void
    {
        Permission::extend(function () {
            Permission::register('view donations')
                ->label('View Donations');

            Permission::register('manage donations')
                ->label('Manage Donations')
                ->children([
                    Permission::make('cancel donations')
                        ->label('Cancel/Pause/Resume Subscriptions'),
                    Permission::make('refund donations')
                        ->label('Refund Payments'),
                ]);
        });
    }

    private function installGlobalSet(): void
    {
        Statamic::afterInstalled(function (): void {
            $this->installGlobal('donation_messages', 'Donation Checkout Messages', [
                'single_heading' => 'Thank you for your donation!',
                'single_message' => 'Your generous contribution makes a real difference.',
                'single_cta_text' => 'Return home',
                'single_cta_url' => '/',
                'recurring_heading' => 'Thank you for your monthly donation!',
                'recurring_message' => 'Your ongoing support helps us plan for the future.',
                'recurring_cta_text' => 'Return home',
                'recurring_cta_url' => '/',
            ]);

            $this->installGlobal('donation_emails', 'Donation Checkout Emails');
        });
    }

    private function installGlobal(string $handle, string $title, array $data = []): void
    {
        if (GlobalSet::findByHandle($handle)) {
            return;
        }

        $set = GlobalSet::make($handle)->title($title);
        $set->save();

        if ($data) {
            $variables = $set->makeLocalization(Statamic::default());
            $variables->data($data);
            $variables->save();
        }
    }
}
