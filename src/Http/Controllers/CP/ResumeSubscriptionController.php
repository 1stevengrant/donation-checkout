<?php

namespace Ghijk\DonationCheckout\Http\Controllers\CP;

use Stripe\StripeClient;
use Illuminate\Http\RedirectResponse;
use Statamic\Http\Controllers\CP\CpController;
use Ghijk\DonationCheckout\Actions\ResumeSubscription;
use Ghijk\DonationCheckout\Concerns\ClearsStripeCache;
use Ghijk\DonationCheckout\Concerns\SendsDonorNotifications;

class ResumeSubscriptionController extends CpController
{
    use ClearsStripeCache, SendsDonorNotifications;

    public function __invoke(string $id, ResumeSubscription $resumeSubscription, StripeClient $stripe): RedirectResponse
    {
        abort_unless(auth()->user()->can('cancel donations'), 403);

        $subscription = $stripe->subscriptions->retrieve($id);
        $resumeSubscription($id);

        $this->clearStripeCache($subscription->customer);
        $this->notifyDonorResumed($subscription->customer);

        return back()->with('success', 'Subscription resumed.');
    }
}
