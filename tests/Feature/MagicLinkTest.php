<?php

use Statamic\Facades\User;
use Ghijk\DonationCheckout\Actions\SendMagicLink;

it('returns success message regardless of whether email exists', function () {
    $this->mock(SendMagicLink::class, function ($mock) {
        $mock->shouldNotReceive('__invoke');
    });

    $mockRepo = Mockery::mock();
    $mockRepo->shouldReceive('findByEmail')
        ->with('nonexistent@example.com')
        ->andReturnNull();
    User::swap($mockRepo);

    $this->post('/donation-checkout/magic-link', ['email' => 'nonexistent@example.com'])
        ->assertRedirect()
        ->assertSessionHas('success');
});

it('validates email is required', function () {
    $this->postJson('/donation-checkout/magic-link', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('validates email format', function () {
    $this->postJson('/donation-checkout/magic-link', ['email' => 'not-an-email'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('portal requires authentication', function () {
    $this->getJson('/donation-checkout/portal')
        ->assertStatus(401);
});

it('sends magic link when user exists with stripe customer', function () {
    $mockUser = Mockery::mock();
    $mockUser->shouldReceive('get')
        ->with('stripe_customer_id')
        ->andReturn('cus_123');

    $mockRepo = Mockery::mock();
    $mockRepo->shouldReceive('findByEmail')
        ->with('donor@example.com')
        ->andReturn($mockUser);
    User::swap($mockRepo);

    $this->mock(SendMagicLink::class, function ($mock) {
        $mock->shouldReceive('__invoke')
            ->once()
            ->with('donor@example.com');
    });

    $this->post('/donation-checkout/magic-link', ['email' => 'donor@example.com'])
        ->assertRedirect()
        ->assertSessionHas('success');
});
