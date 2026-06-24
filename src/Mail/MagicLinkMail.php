<?php

namespace Ghijk\DonationCheckout\Mail;

use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class MagicLinkMail extends DonationMailable
{
    public function __construct(
        public readonly string $url
    ) {
        parent::__construct();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Donation Portal Link',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'donation-checkout::emails.magic-link',
        );
    }
}
