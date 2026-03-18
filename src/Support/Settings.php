<?php

namespace Ghijk\DonationCheckout\Support;

use Statamic\Facades\GlobalSet;

class Settings
{
    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            $globalSet = GlobalSet::findByHandle('donation_messages');
        } catch (\Exception) {
            return $default;
        }

        if ($globalSet) {
            $data = $globalSet->inCurrentSite();

            if ($data) {
                $value = $data->get($key);

                if ($value !== null) {
                    return $value;
                }
            }
        }

        return $default;
    }

    public static function collectBillingAddress(): bool
    {
        return (bool) static::get('collect_billing_address', config('donation-checkout.collect_billing_address', false));
    }

    public static function giftAidEnabled(): bool
    {
        return (bool) static::get('gift_aid_enabled', false);
    }

    public static function giftAidLabel(): string
    {
        return (string) static::get('gift_aid_label', 'Boost your donation by 25% with Gift Aid');
    }

    public static function donorPortalEnabled(): bool
    {
        return (bool) static::get('donor_portal_enabled', config('donation-checkout.donor_portal_enabled', true));
    }

    public static function donorCanCancel(): bool
    {
        return (bool) static::get('donor_can_cancel', config('donation-checkout.donor_can_cancel_subscriptions', true));
    }

    public static function customFields(): array
    {
        $fields = config('donation-checkout.custom_fields', []);

        if (static::giftAidEnabled()) {
            $fields['gift_aid'] = [
                'type' => 'checkbox',
                'label' => static::giftAidLabel(),
            ];
        }

        return $fields;
    }

    public static function pausedEmailSubject(): string
    {
        return (string) static::get('paused_email_subject', 'Thank You for Your Support');
    }

    public static function pausedEmailHeading(): string
    {
        return (string) static::get('paused_email_heading', 'Thank You for Your Support');
    }

    public static function pausedEmailBody(): string
    {
        return (string) static::get('paused_email_body', 'We wanted to reach out to let you know that your recurring donation has been paused. Thank you so much for the support you have given us. Your generosity has made a real difference, and we are truly grateful for every contribution. If you ever wish to resume your donation, you are welcome to do so at any time.');
    }

    public static function resumedEmailSubject(): string
    {
        return (string) static::get('resumed_email_subject', 'Your Donation Has Been Resumed');
    }

    public static function resumedEmailHeading(): string
    {
        return (string) static::get('resumed_email_heading', 'Your Donation Has Been Resumed');
    }

    public static function resumedEmailBody(): string
    {
        return (string) static::get('resumed_email_body', 'Great news! Your recurring donation has been resumed and payments will continue as normal. Thank you for your continued generosity. Your ongoing support makes a real difference.');
    }

    public static function emailGreeting(): string
    {
        return (string) static::get('email_greeting', 'Hi {first_name},');
    }

    public static function resolveGreeting($user): string
    {
        $template = static::emailGreeting();

        $firstName = $user->get('first_name') ?? '';
        $lastName = $user->get('last_name') ?? '';
        $name = mb_trim("{$firstName} {$lastName}") ?: ($user->get('name') ?? '');
        $email = method_exists($user, 'email') ? $user->email() : ($user->get('email') ?? '');

        if (! $firstName && $name) {
            $firstName = explode(' ', $name)[0];
        }

        return str_replace(
            ['{first_name}', '{last_name}', '{name}', '{email}'],
            [$firstName ?: 'there', $lastName, $name ?: $email, $email],
            $template
        );
    }

    public static function singleDonationEmailSubject(): string
    {
        return (string) static::get('single_email_subject', 'Thank You for Your Donation');
    }

    public static function singleDonationEmailHeading(): string
    {
        return (string) static::get('single_email_heading', 'Thank You for Your Donation');
    }

    public static function singleDonationEmailBody(): string
    {
        return (string) static::get('single_email_body', 'Thank you for your generous donation. Your contribution makes a real difference and we are truly grateful for your support.');
    }

    public static function recurringDonationEmailSubject(): string
    {
        return (string) static::get('recurring_email_subject', 'Thank You for Your Monthly Donation');
    }

    public static function recurringDonationEmailHeading(): string
    {
        return (string) static::get('recurring_email_heading', 'Thank You for Your Monthly Donation');
    }

    public static function recurringDonationEmailBody(): string
    {
        return (string) static::get('recurring_email_body', 'Thank you for setting up a recurring donation. Your ongoing support helps us plan for the future and makes a lasting difference.');
    }
}
