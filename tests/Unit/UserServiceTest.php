<?php

use Statamic\Facades\User;
use Ghijk\DonationCheckout\Services\UserService;

it('finds a user by email', function () {
    $mockUser = Mockery::mock();
    $mockRepo = Mockery::mock();
    $mockRepo->shouldReceive('findByEmail')
        ->once()
        ->with('john@example.com')
        ->andReturn($mockUser);

    User::swap($mockRepo);

    $service = new UserService;
    $result = $service->findByEmail('john@example.com');

    expect($result)->toBe($mockUser);
});

it('returns null when user is not found by email', function () {
    $mockRepo = Mockery::mock();
    $mockRepo->shouldReceive('findByEmail')
        ->once()
        ->with('nobody@example.com')
        ->andReturnNull();

    User::swap($mockRepo);

    $service = new UserService;
    $result = $service->findByEmail('nobody@example.com');

    expect($result)->toBeNull();
});

it('creates a user with the given details and a random password', function () {
    $mockUser = Mockery::mock();
    $mockUser->shouldReceive('email')
        ->with('john@example.com')
        ->andReturnSelf();
    $mockUser->shouldReceive('password')
        ->once()
        ->with(Mockery::on(fn (string $password) => mb_strlen($password) === 16))
        ->andReturnSelf();
    $mockUser->shouldReceive('data')
        ->once()
        ->with([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ])
        ->andReturnSelf();
    $mockUser->shouldReceive('save')
        ->once();

    $mockRepo = Mockery::mock();
    $mockRepo->shouldReceive('make')
        ->once()
        ->andReturn($mockUser);

    User::swap($mockRepo);

    $service = new UserService;
    $result = $service->createUser('John', 'Doe', 'john@example.com');

    expect($result)->toBe($mockUser);
});

it('merges data when updating a user instead of replacing all data', function () {
    $mockUser = Mockery::mock();
    $mockUser->shouldReceive('merge')
        ->once()
        ->with(['stripe_customer_id' => 'cus_123']);
    $mockUser->shouldReceive('save')
        ->once();

    $service = new UserService;
    $result = $service->updateUser($mockUser, ['stripe_customer_id' => 'cus_123']);

    expect($result)->toBe($mockUser);
});
