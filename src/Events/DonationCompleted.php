<?php

namespace Ghijk\DonationCheckout\Events;

use Illuminate\Foundation\Events\Dispatchable;

class DonationCompleted
{
    use Dispatchable;

    public function __construct(
        public readonly string $stripeCustomerId,
        public readonly string $sessionId,
        public readonly string $mode,
        public readonly int $amountInCents,
        public readonly string $currency
    ) {}
}
