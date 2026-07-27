<?php

namespace Ghijk\DonationCheckout\Actions;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use Ghijk\DonationCheckout\Mail\MagicLinkMail;

class SendMagicLink
{
    public function __invoke(string $email): void
    {
        $url = URL::temporarySignedRoute(
            'donation-checkout.magic-link.verify',
            now()->addHours(config('donation-checkout.magic_link_expiry_hours', 24)),
            ['email' => $email]
        );

        Mail::to($email)->send(new MagicLinkMail($url));
    }
}
