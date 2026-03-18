<?php

namespace Ghijk\DonationCheckout\Tags;

use Statamic\Tags\Tags;
use Statamic\Facades\GlobalSet;
use Ghijk\DonationCheckout\Support\Settings;
use Ghijk\DonationCheckout\Actions\ListDonations;
use Ghijk\DonationCheckout\Actions\ListSubscriptions;
use Ghijk\DonationCheckout\Actions\RetrieveCheckoutSession;

class Donation extends Tags
{
    protected static $handle = 'donation';

    /**
     * {{ donation:form }} - Renders the complete donation form
     */
    public function form(): string
    {
        $amounts = $this->params->get('amounts', '5|10|50');

        if (is_string($amounts)) {
            $amounts = array_map(intval(...), explode('|', $amounts));
        }

        return view('donation-checkout::form', [
            'amounts' => $amounts,
            'default' => (int) $this->params->get('default', 10),
            'frequency' => $this->params->get('frequency', 'recurring'),
            'currency_symbol' => $this->params->get('currency_symbol', '£'),
            'button_text' => $this->params->get('button_text', 'Donate'),
            'custom_fields' => Settings::customFields(),
        ])->render();
    }

    /**
     * {{ donation:styles }} - Outputs default CSS styles for the donation form
     */
    public function styles(): string
    {
        return view('donation-checkout::styles')->render();
    }

    /**
     * {{ donation:scripts }} - Outputs the vanilla JavaScript component script
     */
    public function scripts(): string
    {
        return view('donation-checkout::scripts', [
            'frequency' => $this->params->get('frequency', 'recurring'),
            'custom_fields' => Settings::customFields(),
        ])->render();
    }

    /**
     * {{ donation:stripe_key }} - Outputs the Stripe publishable key
     */
    public function stripeKey(): string
    {
        return config('donation-checkout.stripe_publishable_key', '');
    }

    /**
     * {{ donation:endpoint }} - Outputs the donation endpoint URL
     */
    public function endpoint(): string
    {
        return url('/donation-checkout/start');
    }

    /**
     * {{ donation:currency }} - Outputs the configured currency code
     */
    public function currency(): string
    {
        return config('donation-checkout.stripe_currency', 'gbp');
    }

    /**
     * {{ donation:thank_you }}...{{ /donation:thank_you }}
     */
    public function thankYou(): array
    {
        $sessionId = request()->query('session_id');

        if (! $sessionId) {
            return [
                'heading' => 'Thank you!',
                'message' => '',
                'amount' => 0,
                'currency' => config('donation-checkout.stripe_currency', 'gbp'),
                'is_recurring' => false,
                'cta_text' => 'Return home',
                'cta_url' => '/',
            ];
        }

        $session = app(RetrieveCheckoutSession::class)($sessionId);
        $isRecurring = $session->mode === 'subscription';
        $prefix = $isRecurring ? 'recurring' : 'single';

        $messages = $this->getThankYouMessages($prefix);

        return [
            'heading' => $messages['heading'],
            'message' => $messages['message'],
            'amount' => $session->amount_total ? $session->amount_total / 100 : 0,
            'currency' => $session->currency ?? config('donation-checkout.stripe_currency', 'gbp'),
            'is_recurring' => $isRecurring,
            'cta_text' => $messages['cta_text'],
            'cta_url' => $messages['cta_url'],
        ];
    }

    /**
     * {{ donation:magic_link_form }} - Renders magic link email form
     */
    public function magicLinkForm(): string
    {
        if (auth()->check()) {
            $portalUrl = url('/donation-checkout/portal');
            $linkText = $this->params->get('portal_text', 'Manage your donations');

            return <<<HTML
            <div class="donation-magic-link">
                <a href="{$portalUrl}" class="donation-portal-link">{$linkText} &rarr;</a>
            </div>
            HTML;
        }

        $action = route('donation-checkout.magic-link.store');
        $buttonText = $this->params->get('button_text', 'Send login link');
        $heading = $this->params->get('heading', 'Already a donor?');

        return <<<HTML
        <div class="donation-magic-link">
            <p class="donation-magic-link-heading">{$heading}</p>
            <form method="POST" action="{$action}" class="donation-magic-link-form">
                <input type="hidden" name="_token" value="{$this->csrfToken()}">
                <div class="donation-field">
                    <label for="donation-magic-link-email">Email</label>
                    <input type="email" name="email" id="donation-magic-link-email" required placeholder="your@email.com">
                </div>
                <div class="donation-submit">
                    <button type="submit" class="donation-submit-btn">{$buttonText}</button>
                </div>
            </form>
        </div>
        HTML;
    }

    /**
     * {{ donation:portal }}...{{ /donation:portal }}
     */
    public function portal(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [
                'authenticated' => false,
                'donations' => [],
                'subscriptions' => [],
            ];
        }

        $customerId = $user->get('stripe_customer_id');

        if (! $customerId) {
            return [
                'authenticated' => true,
                'donations' => [],
                'subscriptions' => [],
            ];
        }

        $donations = collect();
        $subscriptions = collect();

        try {
            $paymentIntents = app(ListDonations::class)($customerId, 100);

            foreach ($paymentIntents->data as $pi) {
                $donations->push([
                    'id' => $pi->id,
                    'amount' => $pi->amount / 100,
                    'currency' => $pi->currency,
                    'status' => $pi->status,
                    'date' => date('Y-m-d H:i', $pi->created),
                ]);
            }

            $subs = app(ListSubscriptions::class)($customerId, 100);

            foreach ($subs->data as $sub) {
                $amount = 0;
                if (isset($sub->items->data[0])) {
                    $amount = ($sub->items->data[0]->price->unit_amount * $sub->items->data[0]->quantity) / 100;
                }

                $subscriptions->push([
                    'id' => $sub->id,
                    'amount' => $amount,
                    'currency' => $sub->currency ?? config('donation-checkout.stripe_currency'),
                    'status' => $sub->status,
                    'paused' => ! empty($sub->pause_collection),
                    'date' => date('Y-m-d H:i', $sub->created),
                    'current_period_end' => date('Y-m-d', $sub->current_period_end),
                    'can_cancel' => Settings::donorCanCancel(),
                    'cancel_url' => url("/donation-checkout/portal/subscriptions/{$sub->id}/cancel"),
                ]);
            }
        } catch (\Exception $e) {
            // Stripe API error
        }

        return [
            'authenticated' => true,
            'donations' => $donations->values()->all(),
            'subscriptions' => $subscriptions->values()->all(),
        ];
    }

    /**
     * {{ donation:portal_cancel_url :id="subscription_id" }}
     */
    public function portalCancelUrl(): string
    {
        $id = $this->params->get('id', '');

        return url("/donation-checkout/portal/subscriptions/{$id}/cancel");
    }

    private function getThankYouMessages(string $prefix): array
    {
        $globalSet = GlobalSet::findByHandle('donation_messages');

        if ($globalSet) {
            $data = $globalSet->inCurrentSite();

            if ($data) {
                $heading = $data->get("{$prefix}_heading");
                $message = $data->get("{$prefix}_message");

                if ($heading || $message) {
                    return [
                        'heading' => $heading ?? 'Thank you!',
                        'message' => $message ?? config("donation-checkout.{$prefix}_donation_success_message"),
                        'cta_text' => $data->get("{$prefix}_cta_text") ?? 'Return home',
                        'cta_url' => $data->get("{$prefix}_cta_url") ?? '/',
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

    private function csrfToken(): string
    {
        return csrf_token();
    }
}
