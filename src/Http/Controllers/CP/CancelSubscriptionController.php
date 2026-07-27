<?php

namespace Ghijk\DonationCheckout\Http\Controllers\CP;

use Stripe\StripeClient;
use Illuminate\Http\RedirectResponse;
use Statamic\Http\Controllers\CP\CpController;
use Ghijk\DonationCheckout\Actions\CancelSubscription;
use Ghijk\DonationCheckout\Concerns\ClearsStripeCache;

class CancelSubscriptionController extends CpController
{
    use ClearsStripeCache;

    public function __invoke(string $id, CancelSubscription $cancelSubscription, StripeClient $stripe): RedirectResponse
    {
        abort_unless(auth()->user()->can('cancel donations'), 403);

        $subscription = $stripe->subscriptions->retrieve($id);
        $cancelSubscription($id);

        $this->clearStripeCache($subscription->customer);

        return back()->with('success', 'Subscription cancelled.');
    }
}
