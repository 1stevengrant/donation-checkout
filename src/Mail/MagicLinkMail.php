<?php

namespace Ghijk\DonationCheckout\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;

class MagicLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $url
    ) {}

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
