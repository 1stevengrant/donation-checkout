<?php

namespace Ghijk\DonationCheckout\Concerns;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Ghijk\DonationCheckout\Support\Settings;
use Ghijk\DonationCheckout\Services\UserService;
use Ghijk\DonationCheckout\Mail\SingleDonationMail;
use Ghijk\DonationCheckout\Mail\RecurringDonationMail;
use Ghijk\DonationCheckout\Mail\SubscriptionPausedMail;
use Ghijk\DonationCheckout\Mail\SubscriptionResumedMail;

trait SendsDonorNotifications
{
    private function notifyDonorPaused(string $stripeCustomerId): void
    {
        $cacheKey = "donation-checkout:notified:paused:{$stripeCustomerId}";

        if (Cache::has($cacheKey)) {
            return;
        }

        $user = app(UserService::class)->findByStripeCustomerId($stripeCustomerId);

        if (! $user) {
            return;
        }

        Cache::put($cacheKey, true, 60);

        Mail::to($user->email())->send(new SubscriptionPausedMail(Settings::resolveGreeting($user)));
    }

    private function notifyDonorResumed(string $stripeCustomerId): void
    {
        $cacheKey = "donation-checkout:notified:resumed:{$stripeCustomerId}";

        if (Cache::has($cacheKey)) {
            return;
        }

        $user = app(UserService::class)->findByStripeCustomerId($stripeCustomerId);

        if (! $user) {
            return;
        }

        Cache::put($cacheKey, true, 60);

        Mail::to($user->email())->send(new SubscriptionResumedMail(Settings::resolveGreeting($user)));
    }

    private function notifyDonationCompleted(string $stripeCustomerId, string $mode, int $amountInCents, string $currency): void
    {
        try {
            $user = app(UserService::class)->findByStripeCustomerId($stripeCustomerId);
        } catch (\Exception) {
            return;
        }

        if (! $user) {
            return;
        }

        $greeting = Settings::resolveGreeting($user);
        $amount = $amountInCents / 100;

        $mail = $mode === 'subscription'
            ? new RecurringDonationMail($greeting, $amount, $currency)
            : new SingleDonationMail($greeting, $amount, $currency);

        Mail::to($user->email())->send($mail);
    }
}
