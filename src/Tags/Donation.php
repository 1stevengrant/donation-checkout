<?php

namespace Ghijk\DonationCheckout\Tags;

use Statamic\Tags\Tags;

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
            'frequency' => $this->frequency(),
            'currency_symbol' => $this->params->get('currency_symbol', '£'),
            'button_text' => $this->params->get('button_text', 'Donate'),
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
            'frequency' => $this->frequency(),
        ])->render();
    }

    private function frequency(): string
    {
        $frequency = $this->params->get(
            'frequency',
            config('donation-checkout.default_frequency', 'recurring')
        );

        return in_array($frequency, ['single', 'recurring'], true) ? $frequency : 'recurring';
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
}
