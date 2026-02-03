<?php

namespace Ghijk\DonationCheckout;

use Ghijk\DonationCheckout\Tags\Donation;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    protected $publishAfterInstall = true;

    protected $tags = [
        Donation::class,
    ];

    protected $routes = [
        'web' => __DIR__.'/../routes/web.php',
    ];

    public function bootAddon()
    {
        Statamic::afterInstalled(function ($command) {
            $command->call('vendor:publish', ['--tag' => 'donation-checkout-config']);
        });

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'donation-checkout');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/donation-checkout'),
        ], 'donation-checkout-views');
    }
}
