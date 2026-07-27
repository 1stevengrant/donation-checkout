<?php

namespace Ghijk\DonationCheckout\Events;

use Illuminate\Foundation\Events\Dispatchable;

class RecurringPaymentSucceeded
{
    use Dispatchable;

    public function __construct(
        public readonly string $stripeCustomerId,
        public readonly string $invoiceId,
        public readonly int $amountInCents
    ) {}
}
