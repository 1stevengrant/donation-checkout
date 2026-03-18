<?php

namespace Ghijk\DonationCheckout\Http\Controllers;

use Statamic\Http\Controllers\Controller;
use Ghijk\DonationCheckout\Support\Settings;
use Ghijk\DonationCheckout\Actions\ListDonations;
use Ghijk\DonationCheckout\Actions\ListSubscriptions;
use Ghijk\DonationCheckout\Concerns\ResolvesPaymentStatus;

class DonorPortalController extends Controller
{
    use ResolvesPaymentStatus;

    public function index(
        ListDonations $listDonations,
        ListSubscriptions $listSubscriptions
    ) {
        $user = auth()->user();
        $customerId = $user->get('stripe_customer_id');

        $donations = collect();
        $subscriptions = collect();

        if ($customerId) {
            try {
                $paymentIntents = $listDonations($customerId, 100);

                foreach ($paymentIntents->data as $pi) {
                    $donations->push([
                        'id' => $pi->id,
                        'amount' => $pi->amount / 100,
                        'currency' => $pi->currency,
                        'status' => $this->donationStatus($pi),
                        'date' => date('j M Y', $pi->created),
                    ]);
                }

                $subs = $listSubscriptions($customerId, 100);

                foreach ($subs->data as $sub) {
                    $amount = 0;
                    if (isset($sub->items->data[0])) {
                        $amount = ($sub->items->data[0]->price->unit_amount * $sub->items->data[0]->quantity) / 100;
                    }

                    $subscriptions->push([
                        'id' => $sub->id,
                        'amount' => $amount,
                        'currency' => $sub->currency ?? config('donation-checkout.stripe_currency'),
                        'status' => $sub->status,
                        'paused' => ! empty($sub->pause_collection),
                        'date' => date('j M Y', $sub->created),
                        'current_period_end' => date('j M Y', $sub->current_period_end),
                        'can_cancel' => Settings::donorCanCancel(),
                        'cancel_url' => url("/donation-checkout/portal/subscriptions/{$sub->id}/cancel"),
                    ]);
                }
            } catch (\Exception $e) {
                // Stripe API error
            }
        }

        $name = mb_trim($user->get('first_name') . ' ' . $user->get('last_name'))
            ?: $user->get('name')
            ?: $user->email();

        return view('donation-checkout::portal', [
            'name' => $name,
            'donations' => $donations->values(),
            'subscriptions' => $subscriptions->values(),
            'currency' => config('donation-checkout.stripe_currency', 'gbp'),
        ]);
    }
}
