<?php

namespace Ghijk\DonationCheckout\Http\Controllers;

use Ghijk\DonationCheckout\Http\Requests\DonationRequest;
use Ghijk\DonationCheckout\Services\PaymentService;
use Ghijk\DonationCheckout\Services\UserService;
use Illuminate\Http\JsonResponse;
use Statamic\Http\Controllers\Controller;

class StartDonationController extends Controller
{
    public function __invoke(
        DonationRequest $donationRequest,
        PaymentService $paymentService,
        UserService $userService
    ): JsonResponse {
        $validated = $donationRequest->validated();

        $user = $userService->findByEmail($validated['email']);

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

        $session = null;

        if ($validated['frequency'] === 'single') {
            $session = $paymentService->singleDonation(
                user: $user,
                amount: $validated['amount']
            );
        }

        if ($validated['frequency'] === 'recurring') {
            $session = $paymentService->recurringDonation(
                user: $user,
                amount: $validated['amount']
            );
        }

        if (! $session) {
            return response()->json([
                'message' => 'Invalid frequency specified.',
                'frequency_received' => $validated['frequency'],
            ], 422);
        }

        return response()->json([
            'url' => $session->url,
        ]);
    }
}
