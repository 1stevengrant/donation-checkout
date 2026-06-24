<?php

namespace Ghijk\DonationCheckout\Http\Controllers\CP;

use Inertia\Inertia;
use Statamic\Facades\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Statamic\Http\Controllers\CP\CpController;
use Ghijk\DonationCheckout\Actions\ListDonations;
use Ghijk\DonationCheckout\Actions\ListSubscriptions;
use Ghijk\DonationCheckout\Concerns\ResolvesPaymentStatus;

class DonationsController extends CpController
{
    use ResolvesPaymentStatus;

    public function index(
        Request $request,
        ListDonations $listDonations,
        ListSubscriptions $listSubscriptions
    ) {
        abort_unless(auth()->user()->can('view donations'), 403);

        $type = $request->query('type');
        $status = $request->query('status');

        $users = User::all()->filter(fn ($user) => $user->get('stripe_customer_id'));

        $donations = collect();
        $subscriptions = collect();

        foreach ($users as $user) {
            $customerId = $user->get('stripe_customer_id');

            try {
                if (! $type || $type === 'single') {
                    $paymentIntents = Cache::remember(
                        "donation-checkout:donations:{$customerId}",
                        120,
                        fn () => $listDonations($customerId, 100)
                    );

                    foreach ($paymentIntents->data as $pi) {
                        $effectiveStatus = $this->donationStatus($pi);

                        if ($status && $effectiveStatus !== $status) {
                            continue;
                        }

                        $donations->push([
                            'id' => $pi->id,
                            'type' => 'single',
                            'amount' => $pi->amount / 100,
                            'currency' => $pi->currency,
                            'status' => $effectiveStatus,
                            'email' => $user->email(),
                            'name' => mb_trim($user->get('first_name') . ' ' . $user->get('last_name')) ?: $user->get('name') ?: $user->email(),
                            'date' => date('Y-m-d H:i', $pi->created),
                            'metadata' => $pi->metadata ? $pi->metadata->toArray() : [],
                            'donor_url' => cp_route('donation-checkout.donor', ['email' => $user->email()]),
                            'refund_url' => cp_route('donation-checkout.payments.refund', $pi->id),
                        ]);
                    }
                }

                if (! $type || $type === 'recurring') {
                    $subs = Cache::remember(
                        "donation-checkout:subscriptions:{$customerId}",
                        120,
                        fn () => $listSubscriptions($customerId, 100)
                    );

                    foreach ($subs->data as $sub) {
                        if ($status && $sub->status !== $status) {
                            continue;
                        }

                        $amount = 0;
                        if (isset($sub->items->data[0])) {
                            $amount = ($sub->items->data[0]->price->unit_amount * $sub->items->data[0]->quantity) / 100;
                        }

                        $subscriptions->push([
                            'id' => $sub->id,
                            'type' => 'recurring',
                            'amount' => $amount,
                            'currency' => $sub->currency ?? config('donation-checkout.stripe_currency'),
                            'status' => $sub->status,
                            'paused' => ! empty($sub->pause_collection),
                            'email' => $user->email(),
                            'name' => mb_trim($user->get('first_name') . ' ' . $user->get('last_name')) ?: $user->get('name') ?: $user->email(),
                            'date' => date('Y-m-d H:i', $sub->created),
                            'current_period_end' => date('Y-m-d', $sub->current_period_end),
                            'metadata' => $sub->metadata ? $sub->metadata->toArray() : [],
                            'donor_url' => cp_route('donation-checkout.donor', ['email' => $user->email()]),
                            'cancel_url' => cp_route('donation-checkout.subscriptions.cancel', $sub->id),
                            'pause_url' => cp_route('donation-checkout.subscriptions.pause', $sub->id),
                            'resume_url' => cp_route('donation-checkout.subscriptions.resume', $sub->id),
                        ]);
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        $all = $donations->merge($subscriptions)->sortByDesc('date')->values();

        return Inertia::render('DonationCheckout/DonationsIndex', [
            'donations' => $all->all(),
            'columns' => [
                ['field' => 'date', 'label' => __('Date'), 'sortable' => true],
                ['field' => 'name', 'label' => __('Donor'), 'sortable' => true],
                ['field' => 'type', 'label' => __('Type'), 'sortable' => true],
                ['field' => 'amount', 'label' => __('Amount'), 'sortable' => true, 'numeric' => true],
                ['field' => 'status', 'label' => __('Status'), 'sortable' => true],
            ],
            'type' => $type,
            'status' => $status,
            'currency' => config('donation-checkout.stripe_currency', 'gbp'),
            'canCancel' => auth()->user()->can('cancel donations'),
            'canRefund' => auth()->user()->can('refund donations'),
        ]);
    }
}
