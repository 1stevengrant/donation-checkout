<?php

namespace Ghijk\DonationCheckout\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Statamic\Http\Controllers\Controller;
use Ghijk\DonationCheckout\Services\UserService;
use Ghijk\DonationCheckout\Actions\CreateSingleDonation;
use Ghijk\DonationCheckout\Actions\CreateStripeCustomer;
use Ghijk\DonationCheckout\Http\Requests\DonationRequest;
use Ghijk\DonationCheckout\Actions\CreateRecurringDonation;

class StartDonationController extends Controller
{
    public function __invoke(
        DonationRequest $donationRequest,
        CreateStripeCustomer $createStripeCustomer,
        CreateSingleDonation $createSingleDonation,
        CreateRecurringDonation $createRecurringDonation,
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

        $stripeCustomerId = $user->stripe_customer_id;

        if (! $stripeCustomerId) {
            $customer = $createStripeCustomer(
                email: $validated['email'],
                name: "{$validated['first_name']} {$validated['last_name']}"
            );

            $stripeCustomerId = $customer->id;

            $userService->updateUser($user, [
                'stripe_customer_id' => $stripeCustomerId,
            ]);
        }

        $session = match ($validated['frequency']) {
            'single' => $createSingleDonation(
                stripeCustomerId: $stripeCustomerId,
                amount: $validated['amount']
            ),
            'recurring' => $createRecurringDonation(
                stripeCustomerId: $stripeCustomerId,
                amount: $validated['amount']
            ),
        };

        return response()->json([
            'url' => $session->url,
        ]);
    }
}
