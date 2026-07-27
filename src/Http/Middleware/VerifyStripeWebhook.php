<?php

namespace Ghijk\DonationCheckout\Http\Middleware;

use Closure;
use Stripe\Webhook;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Stripe\Exception\SignatureVerificationException;

class VerifyStripeWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('donation-checkout.stripe_webhook_secret');

        if (! $secret) {
            abort(500, 'Stripe webhook secret is not configured.');
        }

        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        if (! $signature) {
            abort(403, 'Missing Stripe signature.');
        }

        try {
            $event = Webhook::constructEvent($payload, $signature, $secret);
        } catch (\UnexpectedValueException) {
            abort(400, 'Invalid payload.');
        } catch (SignatureVerificationException) {
            abort(403, 'Invalid signature.');
        }

        $request->attributes->set('stripe_event', $event);

        return $next($request);
    }
}
