<?php

namespace Ghijk\DonationCheckout\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Ghijk\DonationCheckout\Support\Settings;

class SubscriptionPausedMail extends Mailable
{
    use Queueable, SerializesModels;

    public readonly string $subjectLine;

    public readonly string $heading;

    public readonly string $body;

    public function __construct(
        public readonly string $greeting
    ) {
        $this->subjectLine = Settings::pausedEmailSubject();
        $this->heading = Settings::pausedEmailHeading();
        $this->body = Settings::pausedEmailBody();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'donation-checkout::emails.subscription-paused',
        );
    }
}
