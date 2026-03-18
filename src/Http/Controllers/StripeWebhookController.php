<?php

namespace Ghijk\DonationCheckout\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Statamic\Http\Controllers\Controller;
use Ghijk\DonationCheckout\Events\DonationRefunded;
use Ghijk\DonationCheckout\Events\DonationCompleted;
use Ghijk\DonationCheckout\Events\SubscriptionPaused;
use Ghijk\DonationCheckout\Concerns\ClearsStripeCache;
use Ghijk\DonationCheckout\Events\SubscriptionResumed;
use Ghijk\DonationCheckout\Events\SubscriptionUpdated;
use Ghijk\DonationCheckout\Events\SubscriptionCancelled;
use Ghijk\DonationCheckout\Events\RecurringPaymentFailed;
use Ghijk\DonationCheckout\Concerns\SendsDonorNotifications;
use Ghijk\DonationCheckout\Events\RecurringPaymentSucceeded;

class StripeWebhookController extends Controller
{
    use ClearsStripeCache, SendsDonorNotifications;

    public function __invoke(Request $request): JsonResponse
    {
        $event = $request->attributes->get('stripe_event');
        $object = $event->data->object;

        match ($event->type) {
            'checkout.session.completed' => $this->handleCheckoutSessionCompleted($object),
            'customer.subscription.updated' => $this->handleSubscriptionUpdated($object),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($object),
            'charge.refunded' => $this->handleChargeRefunded($object),
            'invoice.payment_failed' => $this->handleInvoicePaymentFailed($object),
            'invoice.payment_succeeded' => $this->handleInvoicePaymentSucceeded($object),
            default => null,
        };

        return response()->json(['status' => 'handled']);
    }

    private function handleCheckoutSessionCompleted(object $session): void
    {
        $customerId = $session->customer;

        $this->clearStripeCache($customerId);

        DonationCompleted::dispatch(
            $customerId,
            $session->id,
            $session->mode,
            $session->amount_total ?? 0,
            $session->currency ?? config('donation-checkout.stripe_currency'),
        );
    }

    private function handleSubscriptionUpdated(object $subscription): void
    {
        $customerId = $subscription->customer;

        $this->clearStripeCache($customerId);

        if (! empty($subscription->pause_collection)) {
            SubscriptionPaused::dispatch($customerId, $subscription->id);
            $this->notifyDonorPaused($customerId);
        } elseif (($subscription->previous_attributes->pause_collection ?? null) !== null) {
            SubscriptionResumed::dispatch($customerId, $subscription->id);
            $this->notifyDonorResumed($customerId);
        } else {
            SubscriptionUpdated::dispatch($customerId, $subscription->id, $subscription->toArray());
        }
    }

    private function handleSubscriptionDeleted(object $subscription): void
    {
        $customerId = $subscription->customer;

        $this->clearStripeCache($customerId);

        SubscriptionCancelled::dispatch($customerId, $subscription->id);
    }

    private function handleChargeRefunded(object $charge): void
    {
        $customerId = $charge->customer;

        $this->clearStripeCache($customerId);

        DonationRefunded::dispatch($customerId, $charge->id, $charge->amount_refunded);
    }

    private function handleInvoicePaymentFailed(object $invoice): void
    {
        $customerId = $invoice->customer;

        $this->clearStripeCache($customerId);

        RecurringPaymentFailed::dispatch($customerId, $invoice->id, $invoice->subscription ?? '');
    }

    private function handleInvoicePaymentSucceeded(object $invoice): void
    {
        $customerId = $invoice->customer;

        $this->clearStripeCache($customerId);

        RecurringPaymentSucceeded::dispatch($customerId, $invoice->id, $invoice->amount_paid ?? 0);
    }
}
