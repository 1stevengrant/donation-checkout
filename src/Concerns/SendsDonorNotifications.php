<?php

namespace Ghijk\DonationCheckout\Concerns;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
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

        Mail::to($user->email())->send(new SubscriptionPausedMail($this->donorName($user)));
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

        Mail::to($user->email())->send(new SubscriptionResumedMail($this->donorName($user)));
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

        $name = $this->donorName($user);
        $amount = $amountInCents / 100;

        $mail = $mode === 'subscription'
            ? new RecurringDonationMail($name, $amount, $currency)
            : new SingleDonationMail($name, $amount, $currency);

        Mail::to($user->email())->send($mail);
    }

    private function donorName($user): string
    {
        return mb_trim($user->get('first_name') . ' ' . $user->get('last_name'))
            ?: $user->get('name')
            ?: 'Supporter';
    }
}
