<?php

use Stripe\Customer;
use Stripe\StripeClient;
use Ghijk\DonationCheckout\Actions\CreateStripeCustomer;

function mockCustomersService(callable $expectations): CreateStripeCustomer
{
    $mockCustomersService = Mockery::mock();
    $expectations($mockCustomersService);

    $mockClient = Mockery::mock(StripeClient::class);
    $mockClient->customers = $mockCustomersService;

    return new CreateStripeCustomer($mockClient);
}

it('creates a stripe customer with the given email and name', function () {
    $expectedCustomer = Mockery::mock(Customer::class);
    $emptySearch = Mockery::mock();
    $emptySearch->shouldReceive('first')->once()->andReturnNull();

    $action = mockCustomersService(function ($mock) use ($expectedCustomer, $emptySearch): void {
        $mock->shouldReceive('all')
            ->once()
            ->with(['email' => 'john@example.com', 'limit' => 1])
            ->andReturn($emptySearch);

        $mock->shouldReceive('create')
            ->once()
            ->with([
                'email' => 'john@example.com',
                'name' => 'John Doe',
            ])
            ->andReturn($expectedCustomer);
    });

    expect($action('john@example.com', 'John Doe'))->toBe($expectedCustomer);
});

it('reuses an existing stripe customer with the same email instead of creating a duplicate', function () {
    $existingCustomer = Customer::constructFrom(['id' => 'cus_existing']);
    $search = Mockery::mock();
    $search->shouldReceive('first')->once()->andReturn($existingCustomer);

    $action = mockCustomersService(function ($mock) use ($search): void {
        $mock->shouldReceive('all')
            ->once()
            ->with(['email' => 'john@example.com', 'limit' => 1])
            ->andReturn($search);

        $mock->shouldNotReceive('create');
    });

    expect($action('john@example.com', 'John Doe'))->toBe($existingCustomer);
});
