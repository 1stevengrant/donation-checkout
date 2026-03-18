<?php

namespace Ghijk\DonationCheckout\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Ghijk\DonationCheckout\Support\Settings;

class RecurringDonationMail extends Mailable
{
    use Queueable, SerializesModels;

    public readonly string $heading;

    public readonly string $body;

    public function __construct(
        public readonly string $donorName,
        public readonly float $amount,
        public readonly string $currency
    ) {
        $this->heading = Settings::recurringDonationEmailHeading();
        $this->body = Settings::recurringDonationEmailBody();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->heading,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'donation-checkout::emails.donation-recurring',
        );
    }
}
