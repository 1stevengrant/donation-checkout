<?php

use Illuminate\Support\Facades\Validator;
use Ghijk\DonationCheckout\Http\Requests\DonationRequest;

function validDonationData(array $overrides = []): array
{
    return array_merge([
        'amount' => 25,
        'email' => 'john@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'frequency' => 'single',
    ], $overrides);
}

function validateDonation(array $data): Illuminate\Validation\Validator
{
    return Validator::make($data, (new DonationRequest)->rules());
}

it('passes with valid data', function () {
    expect(validateDonation(validDonationData())->passes())->toBeTrue();
});

it('requires an amount', function () {
    $validator = validateDonation(validDonationData(['amount' => null]));

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('amount'))->toBeTrue();
});

it('requires amount to be an integer', function () {
    $validator = validateDonation(validDonationData(['amount' => 10.5]));

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('amount'))->toBeTrue();
});

it('rejects string amounts', function () {
    $validator = validateDonation(validDonationData(['amount' => 'ten']));

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('amount'))->toBeTrue();
});

it('rejects amounts less than 1', function () {
    $validator = validateDonation(validDonationData(['amount' => 0]));

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('amount'))->toBeTrue();
});

it('rejects amounts greater than 999999', function () {
    $validator = validateDonation(validDonationData(['amount' => 1000000]));

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('amount'))->toBeTrue();
});

it('accepts boundary amount of 1', function () {
    expect(validateDonation(validDonationData(['amount' => 1]))->passes())->toBeTrue();
});

it('accepts boundary amount of 999999', function () {
    expect(validateDonation(validDonationData(['amount' => 999999]))->passes())->toBeTrue();
});

it('requires a valid email', function () {
    $validator = validateDonation(validDonationData(['email' => 'not-an-email']));

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('email'))->toBeTrue();
});

it('requires first_name', function () {
    $validator = validateDonation(validDonationData(['first_name' => null]));

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('first_name'))->toBeTrue();
});

it('requires last_name', function () {
    $validator = validateDonation(validDonationData(['last_name' => null]));

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('last_name'))->toBeTrue();
});

it('requires frequency', function () {
    $validator = validateDonation(validDonationData(['frequency' => null]));

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('frequency'))->toBeTrue();
});

it('only allows single or recurring frequency', function () {
    $validator = validateDonation(validDonationData(['frequency' => 'weekly']));

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('frequency'))->toBeTrue();
});

it('accepts single frequency', function () {
    expect(validateDonation(validDonationData(['frequency' => 'single']))->passes())->toBeTrue();
});

it('accepts recurring frequency', function () {
    expect(validateDonation(validDonationData(['frequency' => 'recurring']))->passes())->toBeTrue();
});
