<?php

use Stripe\Customer;
use Stripe\StripeClient;
use Ghijk\DonationCheckout\Actions\CreateStripeCustomer;

it('creates a stripe customer with the given email and name', function () {
    $mockCustomersService = Mockery::mock();
    $expectedCustomer = Mockery::mock(Customer::class);

    $mockCustomersService->shouldReceive('create')
        ->once()
        ->with([
            'email' => 'john@example.com',
            'name' => 'John Doe',
        ])
        ->andReturn($expectedCustomer);

    $mockClient = Mockery::mock(StripeClient::class);
    $mockClient->customers = $mockCustomersService;

    $action = new CreateStripeCustomer($mockClient);
    $result = $action('john@example.com', 'John Doe');

    expect($result)->toBe($expectedCustomer);
});
