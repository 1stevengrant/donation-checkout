<?php

namespace Ghijk\DonationCheckout\Concerns;

trait ResolvesPaymentStatus
{
    private function donationStatus(object $pi): string
    {
        if (isset($pi->latest_charge->refunded) && $pi->latest_charge->refunded) {
            return 'refunded';
        }

        if (isset($pi->latest_charge->amount_refunded) && $pi->latest_charge->amount_refunded > 0) {
            return 'partially_refunded';
        }

        return $pi->status;
    }
}
