<?php

namespace Ghijk\DonationCheckout;

use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    protected $publishAfterInstall = true;

    public function bootAddon()
    {
        Statamic::afterInstalled(function ($command) {
            $command->call('vendor:publish', ['--tag' => 'donation-checkout-config']);
        });
    }

    protected $routes = [
        'web' => __DIR__.'/../routes/web.php',
    ];
}
