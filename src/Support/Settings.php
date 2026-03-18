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
}
