<?php

namespace Ghijk\DonationCheckout\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;

class SubscriptionResumedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $donorName
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Donation Has Been Resumed',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'donation-checkout::emails.subscription-resumed',
        );
    }
}
