<?php

namespace Ghijk\DonationCheckout\Concerns;

use Illuminate\Support\Facades\Cache;

trait ClearsStripeCache
{
    private function clearStripeCache(string $customerId): void
    {
        Cache::forget('donation-checkout:widget-stats');
        Cache::forget("donation-checkout:subscriptions:{$customerId}");
        Cache::forget("donation-checkout:donations:{$customerId}");
    }
}
