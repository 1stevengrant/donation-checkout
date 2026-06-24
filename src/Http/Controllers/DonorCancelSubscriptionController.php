<?php

namespace Ghijk\DonationCheckout\Http\Controllers;

use Stripe\StripeClient;
use Illuminate\Http\RedirectResponse;
use Statamic\Http\Controllers\Controller;
use Ghijk\DonationCheckout\Support\Settings;
use Ghijk\DonationCheckout\Actions\CancelSubscription;

class DonorCancelSubscriptionController extends Controller
{
    public function __invoke(
        string $id,
        CancelSubscription $cancelSubscription,
        StripeClient $stripe
    ): RedirectResponse {
        abort_unless(Settings::donorCanCancel(), 403);

        $user = auth()->user();
        $customerId = $user->get('stripe_customer_id');

        abort_unless($customerId, 403);

        $subscription = $stripe->subscriptions->retrieve($id);

        abort_unless($subscription->customer === $customerId, 403);

        $cancelSubscription($id);

        return back()->with('success', 'Your subscription has been cancelled.');
    }
}
