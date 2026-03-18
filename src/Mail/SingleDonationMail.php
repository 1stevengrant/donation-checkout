<?php

namespace Ghijk\DonationCheckout\Mail;

use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Ghijk\DonationCheckout\Support\Settings;

class SingleDonationMail extends DonationMailable
{
    public readonly string $subjectLine;

    public readonly string $heading;

    public readonly string $body;

    public function __construct(
        public readonly string $greeting,
        public readonly float $amount,
        public readonly string $currency
    ) {
        parent::__construct();
        $this->subjectLine = Settings::singleDonationEmailSubject();
        $this->heading = Settings::singleDonationEmailHeading();
        $this->body = Settings::singleDonationEmailBody();
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
            view: 'donation-checkout::emails.donation-single',
        );
    }
}
