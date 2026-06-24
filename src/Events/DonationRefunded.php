<?php

namespace Ghijk\DonationCheckout\Events;

use Illuminate\Foundation\Events\Dispatchable;

class DonationRefunded
{
    use Dispatchable;

    public function __construct(
        public readonly string $stripeCustomerId,
        public readonly string $chargeId,
        public readonly int $amountRefundedInCents
    ) {}
}
