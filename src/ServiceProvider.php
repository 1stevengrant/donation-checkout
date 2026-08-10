<?php

namespace Ghijk\DonationCheckout;

use Statamic\Statamic;
use Stripe\StripeClient;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Ghijk\DonationCheckout\Tags\Donation;
use Illuminate\Support\Facades\RateLimiter;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $publishAfterInstall = true;

    protected $tags = [
        Donation::class,
    ];

    protected $routes = [
        'web' => __DIR__ . '/../routes/web.php',
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

        $this->registerRateLimiter();

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'donation-checkout');

        $this->publishes([
            __DIR__ . '/../config/donation-checkout.php' => config_path('donation-checkout.php'),
        ], 'donation-checkout-config');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/donation-checkout'),
        ], 'donation-checkout-views');
    }

    private function registerRateLimiter(): void
    {
        RateLimiter::for('donation-checkout', fn (Request $request): array => [
            Limit::perMinute((int) config('donation-checkout.rate_limit_per_minute', 5))
                ->by($request->ip()),
            Limit::perDay((int) config('donation-checkout.rate_limit_per_day', 50))
                ->by($request->ip()),
        ]);
    }
}
