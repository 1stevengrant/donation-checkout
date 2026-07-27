<?php

namespace Ghijk\DonationCheckout\Console\Commands;

use Stripe\StripeClient;
use Illuminate\Console\Command;

class SetupStripeWebhookCommand extends Command
{
    protected $signature = 'donation-checkout:setup-webhook
                            {--url= : The full webhook URL (auto-detected from APP_URL if not provided)}
                            {--update : Update an existing webhook instead of creating a new one}';

    protected $description = 'Register the Stripe webhook endpoint and output the signing secret';

    private const EVENTS = [
        'checkout.session.completed',
        'customer.subscription.updated',
        'customer.subscription.deleted',
        'charge.refunded',
        'invoice.payment_failed',
        'invoice.payment_succeeded',
    ];

    public function handle(StripeClient $stripe): int
    {
        $url = $this->option('url') ?: url('/donation-checkout/webhook/stripe');

        $this->info("Webhook URL: {$url}");
        $this->newLine();

        if ($this->option('update')) {
            return $this->updateExisting($stripe, $url);
        }

        return $this->createNew($stripe, $url);
    }

    private function createNew(StripeClient $stripe, string $url): int
    {
        $existing = $this->findExisting($stripe, $url);

        if ($existing) {
            $this->warn('A webhook endpoint already exists for this URL.');
            $this->line("  ID: {$existing->id}");
            $this->line("  Status: {$existing->status}");
            $this->newLine();
            $this->line('Run with --update to update its events, or delete it in the Stripe Dashboard first.');

            return self::FAILURE;
        }

        $this->info('Creating Stripe webhook endpoint...');

        $endpoint = $stripe->webhookEndpoints->create([
            'url' => $url,
            'enabled_events' => self::EVENTS,
            'description' => 'Donation Checkout (Statamic addon)',
        ]);

        $this->writeEnvSecret($endpoint->secret);

        $this->newLine();
        $this->components->info('Webhook created successfully.');
        $this->newLine();

        $this->components->twoColumnDetail('Endpoint ID', $endpoint->id);
        $this->components->twoColumnDetail('Status', $endpoint->status);
        $this->newLine();

        $this->table(['Event'], array_map(fn ($e) => [$e], self::EVENTS));

        return self::SUCCESS;
    }

    private function updateExisting(StripeClient $stripe, string $url): int
    {
        $existing = $this->findExisting($stripe, $url);

        if (! $existing) {
            $this->error('No existing webhook endpoint found for this URL.');
            $this->line('Run without --update to create a new one.');

            return self::FAILURE;
        }

        $stripe->webhookEndpoints->update($existing->id, [
            'enabled_events' => self::EVENTS,
            'url' => $url,
            'description' => 'Donation Checkout (Statamic addon)',
        ]);

        $this->components->info("Webhook {$existing->id} updated.");
        $this->newLine();
        $this->table(['Event'], array_map(fn ($e) => [$e], self::EVENTS));

        return self::SUCCESS;
    }

    private function writeEnvSecret(string $secret): void
    {
        $envPath = app()->environmentFilePath();

        if (! file_exists($envPath)) {
            $this->warn('.env file not found, add this manually:');
            $this->line("  STRIPE_WEBHOOK_SECRET={$secret}");

            return;
        }

        $contents = file_get_contents($envPath);

        if (str_contains($contents, 'STRIPE_WEBHOOK_SECRET=')) {
            $contents = preg_replace(
                '/^STRIPE_WEBHOOK_SECRET=.*$/m',
                "STRIPE_WEBHOOK_SECRET={$secret}",
                $contents
            );
        } else {
            $contents = mb_rtrim($contents) . "\nSTRIPE_WEBHOOK_SECRET={$secret}\n";
        }

        file_put_contents($envPath, $contents);

        $this->components->info('STRIPE_WEBHOOK_SECRET written to .env');
    }

    private function findExisting(StripeClient $stripe, string $url): ?object
    {
        $endpoints = $stripe->webhookEndpoints->all(['limit' => 100]);

        foreach ($endpoints->data as $endpoint) {
            if ($endpoint->url === $url) {
                return $endpoint;
            }
        }

        return null;
    }
}
