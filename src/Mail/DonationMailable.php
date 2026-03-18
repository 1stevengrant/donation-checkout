<?php

namespace Ghijk\DonationCheckout\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Ghijk\DonationCheckout\Support\Settings;

abstract class DonationMailable extends Mailable
{
    use Queueable, SerializesModels;

    public string $logoUrl;

    public string $orgName;

    public string $accentColor;

    public function __construct()
    {
        $this->logoUrl = Settings::emailLogoUrl();
        $this->orgName = Settings::emailOrgName();
        $this->accentColor = Settings::emailAccentColor();
    }
}
