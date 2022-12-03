<?php

namespace Ghijk\DonationCheckout\Http\Controllers;

use Ghijk\DonationCheckout\Http\Requests\DonationRequest;
use Ghijk\DonationCheckout\Services\PaymentService;
use Ghijk\DonationCheckout\Services\UserService;
use Statamic\Http\Controllers\Controller;

class StartDonationController extends Controller
{
    public function __invoke(
        DonationRequest $donationRequest,
        PaymentService $paymentService,
        UserService $userService
    ) {
        $validated = $donationRequest->validated();

        ray('request validated');

        $user = $userService->findByEmail($validated['email']);

        ray($user);

        if (! $user) {
            $user = $userService->createUser(
                firstName: $validated['first_name'],
                lastName: $validated['last_name'],
                email: $validated['email']
            );
        }

        if (! $user->stripe_customer_id) {
            $userService->updateUser(
                $user,
                [
                    'stripe_customer_id' => $paymentService->createCustomer(
                        email: $validated['email'],
                        name: $validated['first_name'].' '.$validated['last_name']
                    )->id,
                ]
            );
        }

        if ($validated['frequency'] === 'single') {
            return $paymentService->singleDonation(
                user: $user,
                amount: $validated['amount']
            );
        }

        if ($validated['frequency'] === 'recurring') {
            return $paymentService->recurringDonation(
                user: $user,
                amount: $validated['amount']
            );
        }
    }
}
