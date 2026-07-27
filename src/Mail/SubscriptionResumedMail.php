<?php

namespace Ghijk\DonationCheckout\Mail;

use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Ghijk\DonationCheckout\Support\Settings;

class SubscriptionResumedMail extends DonationMailable
{
    public readonly string $subjectLine;

    public readonly string $heading;

    public readonly string $body;

    public function __construct(
        public readonly string $greeting
    ) {
        parent::__construct();
        $this->subjectLine = Settings::resumedEmailSubject();
        $this->heading = Settings::resumedEmailHeading();
        $this->body = Settings::resumedEmailBody();
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
            view: 'donation-checkout::emails.subscription-resumed',
        );
    }
}
