<?php

namespace Ghijk\DonationCheckout\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;
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

        $user = null;
        $stripeCustomerId = null;

        if (config('donation-checkout.create_users', true)) {
            $user = $userService->findByEmail($validated['email']);

            if (! $user) {
                $user = $userService->createUser(
                    firstName: $validated['first_name'],
                    lastName: $validated['last_name'],
                    email: $validated['email']
                );
            }

            $stripeCustomerId = $user->stripe_customer_id;
        }

        try {
            if (! $stripeCustomerId) {
                $customer = $createStripeCustomer(
                    email: $validated['email'],
                    name: "{$validated['first_name']} {$validated['last_name']}"
                );

                $stripeCustomerId = $customer->id;

                if ($user) {
                    $userService->updateUser($user, [
                        'stripe_customer_id' => $stripeCustomerId,
                    ]);
                }
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
        } catch (ApiErrorException $exception) {
            Log::error('Donation checkout session could not be created.', [
                'frequency' => $validated['frequency'],
                'stripe_error' => $exception->getMessage(),
                'stripe_request_id' => $exception->getRequestId(),
            ]);

            return response()->json([
                'message' => 'We could not start your donation right now. Please try again shortly.',
            ], 502);
        }

        return response()->json([
            'url' => $session->url,
        ]);
    }
}
