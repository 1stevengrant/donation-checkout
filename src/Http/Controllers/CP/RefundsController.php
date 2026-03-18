<?php

namespace Ghijk\DonationCheckout\Http\Controllers\CP;

use Stripe\StripeClient;
use Illuminate\Http\RedirectResponse;
use Statamic\Http\Controllers\CP\CpController;
use Ghijk\DonationCheckout\Actions\RefundPayment;
use Ghijk\DonationCheckout\Concerns\ClearsStripeCache;

class RefundsController extends CpController
{
    use ClearsStripeCache;

    public function store(string $id, RefundPayment $refundPayment, StripeClient $stripe): RedirectResponse
    {
        abort_unless(auth()->user()->can('refund donations'), 403);

        $paymentIntent = $stripe->paymentIntents->retrieve($id);
        $refundPayment($id);

        $this->clearStripeCache($paymentIntent->customer);

        return back()->with('success', 'Payment refunded.');
    }
}
