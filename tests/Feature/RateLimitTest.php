<?php

use Illuminate\Http\Request;
use Ghijk\DonationCheckout\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    (new ServiceProvider($this->app))->bootAddon();
});

it('registers the donation-checkout rate limiter', function () {
    expect(RateLimiter::limiter('donation-checkout'))->not->toBeNull();
});

it('limits donations per ip using the configured thresholds', function () {
    config()->set('donation-checkout.rate_limit_per_minute', 3);
    config()->set('donation-checkout.rate_limit_per_day', 7);

    (new ServiceProvider($this->app))->bootAddon();

    $limits = (RateLimiter::limiter('donation-checkout'))(Request::create('/donation-checkout/start', 'POST'));

    expect($limits)->toHaveCount(2)
        ->and($limits[0]->maxAttempts)->toBe(3)
        ->and($limits[0]->decaySeconds)->toBe(60)
        ->and($limits[1]->maxAttempts)->toBe(7)
        ->and($limits[1]->decaySeconds)->toBe(60 * 60 * 24);
});

it('scopes the rate limit to the requesting ip', function () {
    $limits = (RateLimiter::limiter('donation-checkout'))(
        Request::create('/donation-checkout/start', 'POST', server: ['REMOTE_ADDR' => '203.0.113.7'])
    );

    expect($limits[0]->key)->toStartWith('203.0.113.7');
});
