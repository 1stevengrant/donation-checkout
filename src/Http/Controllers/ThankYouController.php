<?php

namespace Ghijk\DonationCheckout\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\Facades\GlobalSet;
use Statamic\Http\Controllers\Controller;
use Ghijk\DonationCheckout\Actions\RetrieveCheckoutSession;

class ThankYouController extends Controller
{
    public function __invoke(Request $request, RetrieveCheckoutSession $retrieveSession)
    {
        $sessionId = $request->query('session_id');
        abort_unless($sessionId, 404);

        $session = $retrieveSession($sessionId);

        $isRecurring = $session->mode === 'subscription';
        $prefix = $isRecurring ? 'recurring' : 'single';

        $messages = $this->getMessages($prefix);

        $amount = 0;
        if ($session->amount_total) {
            $amount = $session->amount_total / 100;
        }

        return view('donation-checkout::thank-you', [
            'amount' => $amount,
            'currency' => $session->currency ?? config('donation-checkout.stripe_currency', 'gbp'),
            'heading' => $messages['heading'],
            'message' => $messages['message'],
            'cta_text' => $messages['cta_text'],
            'cta_url' => $messages['cta_url'],
            'is_recurring' => $isRecurring,
            'session' => $session,
        ]);
    }

    private function getMessages(string $prefix): array
    {
        $globalSet = GlobalSet::findByHandle('donation_messages');

        if ($globalSet) {
            $data = $globalSet->inCurrentSite();

            if ($data) {
                $heading = $data->get("{$prefix}_heading");
                $message = $data->get("{$prefix}_message");
                $ctaText = $data->get("{$prefix}_cta_text");
                $ctaUrl = $data->get("{$prefix}_cta_url");

                if ($heading || $message) {
                    return [
                        'heading' => $heading ?? 'Thank you!',
                        'message' => $message ?? config("donation-checkout.{$prefix}_donation_success_message"),
                        'cta_text' => $ctaText ?? 'Return home',
                        'cta_url' => $ctaUrl ?? '/',
                    ];
                }
            }
        }

        return [
            'heading' => 'Thank you!',
            'message' => config("donation-checkout.{$prefix}_donation_success_message"),
            'cta_text' => 'Return home',
            'cta_url' => '/',
        ];
    }
}
