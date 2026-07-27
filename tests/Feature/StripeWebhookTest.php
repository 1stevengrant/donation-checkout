<?php

use Illuminate\Support\Facades\Event;
use Ghijk\DonationCheckout\Events\DonationRefunded;
use Ghijk\DonationCheckout\Events\DonationCompleted;
use Ghijk\DonationCheckout\Events\SubscriptionCancelled;
use Ghijk\DonationCheckout\Events\RecurringPaymentFailed;
use Ghijk\DonationCheckout\Events\RecurringPaymentSucceeded;

function signPayload(string $payload, string $secret = 'whsec_test_secret'): string
{
    $timestamp = time();
    $signature = hash_hmac('sha256', "{$timestamp}.{$payload}", $secret);

    return "t={$timestamp},v1={$signature}";
}

it('rejects requests without stripe signature', function () {
    $this->postJson('/donation-checkout/webhook/stripe', ['type' => 'test'])
        ->assertStatus(403);
});

it('rejects requests with invalid signature', function () {
    $this->postJson('/donation-checkout/webhook/stripe', ['type' => 'test'], [
        'Stripe-Signature' => 't=123,v1=invalid',
    ])->assertStatus(403);
});

it('returns 200 for unrecognised event types', function () {
    $payload = json_encode([
        'id' => 'evt_test',
        'type' => 'some.unknown.event',
        'data' => ['object' => []],
        'api_version' => '2023-10-16',
    ]);

    $this->call('POST', '/donation-checkout/webhook/stripe', [], [], [], [
        'HTTP_STRIPE_SIGNATURE' => signPayload($payload),
        'CONTENT_TYPE' => 'application/json',
    ], $payload)->assertOk();
});

it('dispatches DonationCompleted on checkout.session.completed', function () {
    Event::fake([DonationCompleted::class]);

    $payload = json_encode([
        'id' => 'evt_test',
        'type' => 'checkout.session.completed',
        'data' => ['object' => [
            'id' => 'cs_test_123',
            'customer' => 'cus_123',
            'mode' => 'payment',
            'amount_total' => 2500,
            'currency' => 'gbp',
        ]],
        'api_version' => '2023-10-16',
    ]);

    $this->call('POST', '/donation-checkout/webhook/stripe', [], [], [], [
        'HTTP_STRIPE_SIGNATURE' => signPayload($payload),
        'CONTENT_TYPE' => 'application/json',
    ], $payload)->assertOk();

    Event::assertDispatched(DonationCompleted::class, function ($event) {
        return $event->stripeCustomerId === 'cus_123'
            && $event->sessionId === 'cs_test_123'
            && $event->mode === 'payment'
            && $event->amountInCents === 2500;
    });
});

it('dispatches SubscriptionCancelled on customer.subscription.deleted', function () {
    Event::fake([SubscriptionCancelled::class]);

    $payload = json_encode([
        'id' => 'evt_test',
        'type' => 'customer.subscription.deleted',
        'data' => ['object' => [
            'id' => 'sub_123',
            'customer' => 'cus_123',
        ]],
        'api_version' => '2023-10-16',
    ]);

    $this->call('POST', '/donation-checkout/webhook/stripe', [], [], [], [
        'HTTP_STRIPE_SIGNATURE' => signPayload($payload),
        'CONTENT_TYPE' => 'application/json',
    ], $payload)->assertOk();

    Event::assertDispatched(SubscriptionCancelled::class, function ($event) {
        return $event->stripeCustomerId === 'cus_123'
            && $event->subscriptionId === 'sub_123';
    });
});

it('dispatches DonationRefunded on charge.refunded', function () {
    Event::fake([DonationRefunded::class]);

    $payload = json_encode([
        'id' => 'evt_test',
        'type' => 'charge.refunded',
        'data' => ['object' => [
            'id' => 'ch_123',
            'customer' => 'cus_123',
            'amount_refunded' => 2500,
        ]],
        'api_version' => '2023-10-16',
    ]);

    $this->call('POST', '/donation-checkout/webhook/stripe', [], [], [], [
        'HTTP_STRIPE_SIGNATURE' => signPayload($payload),
        'CONTENT_TYPE' => 'application/json',
    ], $payload)->assertOk();

    Event::assertDispatched(DonationRefunded::class, function ($event) {
        return $event->stripeCustomerId === 'cus_123'
            && $event->chargeId === 'ch_123'
            && $event->amountRefundedInCents === 2500;
    });
});

it('dispatches RecurringPaymentFailed on invoice.payment_failed', function () {
    Event::fake([RecurringPaymentFailed::class]);

    $payload = json_encode([
        'id' => 'evt_test',
        'type' => 'invoice.payment_failed',
        'data' => ['object' => [
            'id' => 'in_123',
            'customer' => 'cus_123',
            'subscription' => 'sub_123',
        ]],
        'api_version' => '2023-10-16',
    ]);

    $this->call('POST', '/donation-checkout/webhook/stripe', [], [], [], [
        'HTTP_STRIPE_SIGNATURE' => signPayload($payload),
        'CONTENT_TYPE' => 'application/json',
    ], $payload)->assertOk();

    Event::assertDispatched(RecurringPaymentFailed::class, function ($event) {
        return $event->stripeCustomerId === 'cus_123'
            && $event->invoiceId === 'in_123'
            && $event->subscriptionId === 'sub_123';
    });
});

it('dispatches RecurringPaymentSucceeded on invoice.payment_succeeded', function () {
    Event::fake([RecurringPaymentSucceeded::class]);

    $payload = json_encode([
        'id' => 'evt_test',
        'type' => 'invoice.payment_succeeded',
        'data' => ['object' => [
            'id' => 'in_123',
            'customer' => 'cus_123',
            'amount_paid' => 1000,
        ]],
        'api_version' => '2023-10-16',
    ]);

    $this->call('POST', '/donation-checkout/webhook/stripe', [], [], [], [
        'HTTP_STRIPE_SIGNATURE' => signPayload($payload),
        'CONTENT_TYPE' => 'application/json',
    ], $payload)->assertOk();

    Event::assertDispatched(RecurringPaymentSucceeded::class, function ($event) {
        return $event->stripeCustomerId === 'cus_123'
            && $event->invoiceId === 'in_123'
            && $event->amountInCents === 1000;
    });
});
