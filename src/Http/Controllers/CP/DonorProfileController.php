<?php

namespace Ghijk\DonationCheckout\Http\Controllers\CP;

use Inertia\Inertia;
use Statamic\Facades\User;
use Statamic\CP\Breadcrumbs\Breadcrumb;
use Statamic\CP\Breadcrumbs\Breadcrumbs;
use Statamic\Http\Controllers\CP\CpController;
use Ghijk\DonationCheckout\Actions\ListDonations;
use Ghijk\DonationCheckout\Actions\ListSubscriptions;
use Ghijk\DonationCheckout\Concerns\ResolvesPaymentStatus;

class DonorProfileController extends CpController
{
    use ResolvesPaymentStatus;

    public function show(
        string $email,
        ListDonations $listDonations,
        ListSubscriptions $listSubscriptions
    ) {
        abort_unless(auth()->user()->can('view donations'), 403);

        $user = User::findByEmail($email);
        abort_unless($user, 404);

        $customerId = $user->get('stripe_customer_id');
        abort_unless($customerId, 404);

        $name = mb_trim($user->get('first_name') . ' ' . $user->get('last_name'))
            ?: $user->get('name')
            ?: $user->email();

        Breadcrumbs::push(new Breadcrumb(text: $name));

        $donations = collect();
        $subscriptions = collect();

        try {
            $paymentIntents = $listDonations($customerId, 100);

            foreach ($paymentIntents->data as $pi) {
                $donations->push([
                    'id' => $pi->id,
                    'amount' => $pi->amount / 100,
                    'currency' => $pi->currency,
                    'status' => $this->donationStatus($pi),
                    'date' => date('Y-m-d H:i', $pi->created),
                    'metadata' => $pi->metadata ? $pi->metadata->toArray() : [],
                    'refund_url' => cp_route('donation-checkout.payments.refund', $pi->id),
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
                    'date' => date('Y-m-d H:i', $sub->created),
                    'current_period_end' => date('Y-m-d', $sub->current_period_end),
                    'metadata' => $sub->metadata ? $sub->metadata->toArray() : [],
                    'cancel_url' => cp_route('donation-checkout.subscriptions.cancel', $sub->id),
                    'pause_url' => cp_route('donation-checkout.subscriptions.pause', $sub->id),
                    'resume_url' => cp_route('donation-checkout.subscriptions.resume', $sub->id),
                ]);
            }
        } catch (\Exception $e) {
            // Stripe API error, show what we can
        }

        return Inertia::render('DonationCheckout/DonorProfile', [
            'user' => [
                'name' => $name,
                'email' => $user->email(),
            ],
            'donations' => $donations->values()->all(),
            'donationColumns' => [
                ['field' => 'date', 'label' => __('Date'), 'sortable' => true],
                ['field' => 'amount', 'label' => __('Amount'), 'sortable' => true, 'numeric' => true],
                ['field' => 'status', 'label' => __('Status'), 'sortable' => true],
                ['field' => 'metadata', 'label' => __('Metadata'), 'sortable' => false],
            ],
            'subscriptions' => $subscriptions->values()->all(),
            'subscriptionColumns' => [
                ['field' => 'date', 'label' => __('Date'), 'sortable' => true],
                ['field' => 'amount', 'label' => __('Amount'), 'sortable' => true, 'numeric' => true],
                ['field' => 'status', 'label' => __('Status'), 'sortable' => true],
                ['field' => 'current_period_end', 'label' => __('Next Payment'), 'sortable' => true],
                ['field' => 'metadata', 'label' => __('Metadata'), 'sortable' => false],
            ],
            'currency' => config('donation-checkout.stripe_currency', 'gbp'),
            'canCancel' => auth()->user()->can('cancel donations'),
            'canRefund' => auth()->user()->can('refund donations'),
        ]);
    }
}
