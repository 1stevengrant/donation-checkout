<?php

use Stripe\Customer;
use Stripe\Checkout\Session;
use Ghijk\DonationCheckout\Services\UserService;
use Ghijk\DonationCheckout\Actions\CreateSingleDonation;
use Ghijk\DonationCheckout\Actions\CreateStripeCustomer;
use Ghijk\DonationCheckout\Actions\CreateRecurringDonation;

function donationPayload(array $overrides = []): array
{
    return array_merge([
        'amount' => 25,
        'email' => 'john@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'frequency' => 'single',
    ], $overrides);
}

it('returns validation errors for missing fields', function () {
    $this->postJson('/donation-checkout/start', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['amount', 'email', 'first_name', 'last_name', 'frequency']);
});

it('rejects invalid frequency values', function () {
    $this->postJson('/donation-checkout/start', donationPayload(['frequency' => 'weekly']))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['frequency']);
});

it('rejects decimal amounts', function () {
    $this->postJson('/donation-checkout/start', donationPayload(['amount' => 10.5]))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['amount']);
});

it('rejects negative amounts', function () {
    $this->postJson('/donation-checkout/start', donationPayload(['amount' => -5]))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['amount']);
});

it('rejects zero amounts', function () {
    $this->postJson('/donation-checkout/start', donationPayload(['amount' => 0]))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['amount']);
});

it('creates a new user and stripe customer for first-time donors', function () {
    $mockUser = new class
    {
        public ?string $stripe_customer_id = null;
    };

    $customer = Customer::constructFrom(['id' => 'cus_new']);
    $session = Session::constructFrom(['url' => 'https://checkout.stripe.com/session_123']);

    $this->mock(UserService::class, function ($mock) use ($mockUser) {
        $mock->shouldReceive('findByEmail')
            ->with('john@example.com')
            ->once()
            ->andReturnNull();

        $mock->shouldReceive('createUser')
            ->with('John', 'Doe', 'john@example.com')
            ->once()
            ->andReturn($mockUser);

        $mock->shouldReceive('updateUser')
            ->once();
    });

    $this->mock(CreateStripeCustomer::class, function ($mock) use ($customer) {
        $mock->shouldReceive('__invoke')
            ->with('john@example.com', 'John Doe')
            ->once()
            ->andReturn($customer);
    });

    $this->mock(CreateSingleDonation::class, function ($mock) use ($session) {
        $mock->shouldReceive('__invoke')
            ->once()
            ->andReturn($session);
    });

    $this->postJson('/donation-checkout/start', donationPayload())
        ->assertOk()
        ->assertJson(['url' => 'https://checkout.stripe.com/session_123']);
});

it('reuses existing user and stripe customer for returning donors', function () {
    $mockUser = new class
    {
        public string $stripe_customer_id = 'cus_existing';
    };

    $session = Session::constructFrom(['url' => 'https://checkout.stripe.com/session_456']);

    $this->mock(UserService::class, function ($mock) use ($mockUser) {
        $mock->shouldReceive('findByEmail')
            ->with('john@example.com')
            ->once()
            ->andReturn($mockUser);

        $mock->shouldNotReceive('createUser');
        $mock->shouldNotReceive('updateUser');
    });

    $this->mock(CreateStripeCustomer::class, function ($mock) {
        $mock->shouldNotReceive('__invoke');
    });

    $this->mock(CreateRecurringDonation::class, function ($mock) use ($session) {
        $mock->shouldReceive('__invoke')
            ->once()
            ->andReturn($session);
    });

    $this->postJson('/donation-checkout/start', donationPayload(['frequency' => 'recurring']))
        ->assertOk()
        ->assertJson(['url' => 'https://checkout.stripe.com/session_456']);
});

it('creates a stripe customer for existing user without one', function () {
    $mockUser = new class
    {
        public ?string $stripe_customer_id = null;
    };

    $customer = Customer::constructFrom(['id' => 'cus_new_for_existing']);
    $session = Session::constructFrom(['url' => 'https://checkout.stripe.com/session_789']);

    $this->mock(UserService::class, function ($mock) use ($mockUser) {
        $mock->shouldReceive('findByEmail')
            ->with('john@example.com')
            ->once()
            ->andReturn($mockUser);

        $mock->shouldNotReceive('createUser');

        $mock->shouldReceive('updateUser')
            ->once();
    });

    $this->mock(CreateStripeCustomer::class, function ($mock) use ($customer) {
        $mock->shouldReceive('__invoke')
            ->with('john@example.com', 'John Doe')
            ->once()
            ->andReturn($customer);
    });

    $this->mock(CreateSingleDonation::class, function ($mock) use ($session) {
        $mock->shouldReceive('__invoke')
            ->once()
            ->andReturn($session);
    });

    $this->postJson('/donation-checkout/start', donationPayload())
        ->assertOk()
        ->assertJson(['url' => 'https://checkout.stripe.com/session_789']);
});

it('handles single frequency donations', function () {
    $mockUser = new class
    {
        public string $stripe_customer_id = 'cus_123';
    };

    $session = Session::constructFrom(['url' => 'https://checkout.stripe.com/single']);

    $this->mock(UserService::class, function ($mock) use ($mockUser) {
        $mock->shouldReceive('findByEmail')->andReturn($mockUser);
    });

    $this->mock(CreateSingleDonation::class, function ($mock) use ($session) {
        $mock->shouldReceive('__invoke')->once()->andReturn($session);
    });

    $this->mock(CreateRecurringDonation::class, function ($mock) {
        $mock->shouldNotReceive('__invoke');
    });

    $this->postJson('/donation-checkout/start', donationPayload(['frequency' => 'single']))
        ->assertOk()
        ->assertJson(['url' => 'https://checkout.stripe.com/single']);
});

it('handles recurring frequency donations', function () {
    $mockUser = new class
    {
        public string $stripe_customer_id = 'cus_123';
    };

    $session = Session::constructFrom(['url' => 'https://checkout.stripe.com/recurring']);

    $this->mock(UserService::class, function ($mock) use ($mockUser) {
        $mock->shouldReceive('findByEmail')->andReturn($mockUser);
    });

    $this->mock(CreateRecurringDonation::class, function ($mock) use ($session) {
        $mock->shouldReceive('__invoke')->once()->andReturn($session);
    });

    $this->mock(CreateSingleDonation::class, function ($mock) {
        $mock->shouldNotReceive('__invoke');
    });

    $this->postJson('/donation-checkout/start', donationPayload(['frequency' => 'recurring']))
        ->assertOk()
        ->assertJson(['url' => 'https://checkout.stripe.com/recurring']);
});
