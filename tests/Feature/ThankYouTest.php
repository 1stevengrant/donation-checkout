<?php

use Stripe\Checkout\Session;
use Statamic\Facades\GlobalSet;
use Ghijk\DonationCheckout\Actions\RetrieveCheckoutSession;

it('returns 404 without session_id', function () {
    $this->get('/donation-checkout/thank-you')
        ->assertStatus(404);
});

it('renders thank you page for a single donation', function () {
    $session = Session::constructFrom([
        'id' => 'cs_test_123',
        'mode' => 'payment',
        'amount_total' => 2500,
        'currency' => 'gbp',
    ]);

    $this->mock(RetrieveCheckoutSession::class, function ($mock) use ($session) {
        $mock->shouldReceive('__invoke')
            ->once()
            ->with('cs_test_123')
            ->andReturn($session);
    });

    $mockRepo = Mockery::mock();
    $mockRepo->shouldReceive('findByHandle')
        ->with('donation_messages')
        ->andReturnNull();
    GlobalSet::swap($mockRepo);

    $this->get('/donation-checkout/thank-you?session_id=cs_test_123')
        ->assertOk()
        ->assertSee('Thank you!')
        ->assertSee('25.00');
});

it('renders thank you page for a recurring donation', function () {
    $session = Session::constructFrom([
        'id' => 'cs_test_456',
        'mode' => 'subscription',
        'amount_total' => 1000,
        'currency' => 'gbp',
    ]);

    $this->mock(RetrieveCheckoutSession::class, function ($mock) use ($session) {
        $mock->shouldReceive('__invoke')
            ->once()
            ->with('cs_test_456')
            ->andReturn($session);
    });

    $mockRepo = Mockery::mock();
    $mockRepo->shouldReceive('findByHandle')
        ->with('donation_messages')
        ->andReturnNull();
    GlobalSet::swap($mockRepo);

    $this->get('/donation-checkout/thank-you?session_id=cs_test_456')
        ->assertOk()
        ->assertSee('Thank you!')
        ->assertSee('10.00')
        ->assertSee('per month');
});
