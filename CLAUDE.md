# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Statamic addon that integrates Stripe Checkout for handling variable single and recurring donations. It uses the Stripe PHP SDK and extends Statamic's AddonServiceProvider.

## Commands

```bash
# Run tests
composer test

# Run tests with coverage
composer test-coverage

# Format code with Laravel Pint
composer format
```

## Architecture

### Entry Point
- `ServiceProvider.php` extends `Statamic\Providers\AddonServiceProvider` - registers routes, tags, views, and auto-publishes config on installation

### Statamic Tags
`src/Tags/Donation.php` provides template tags:
- `{{ donation:form }}` - Complete donation form with Alpine.js integration
- `{{ donation:scripts }}` - Alpine.js component (place before closing `</body>`)
- `{{ donation:stripe_key }}` - Stripe publishable key
- `{{ donation:endpoint }}` - Donation API endpoint URL
- `{{ donation:currency }}` - Configured currency code

Form tag parameters: `amounts`, `default`, `frequency`, `currency_symbol`, `button_text`

### Views
`resources/views/` - Publishable Blade templates:
- `form.blade.php` - Donation form markup
- `scripts.blade.php` - Alpine.js donation handler

### Request Flow
1. POST to `/donation-checkout/start` hits `StartDonationController` (invokable)
2. `DonationRequest` normalises input (trims, lowercases email) and validates: amount, email, first_name, last_name, frequency (single|recurring)
3. When `create_users` is enabled, `UserService` finds or creates a Statamic user by email
4. `CreateStripeCustomer` reuses the Stripe customer matching the email, or creates one
5. `CreateSingleDonation` / `CreateRecurringDonation` creates the Checkout Session
6. Returns `{"url": ...}` (frontend redirects there), or a generic 502 if Stripe errors

### Actions & Services
- `CreateStripeCustomer` - finds an existing Stripe customer by email, otherwise creates one
- `CreateSingleDonation` - payment-mode Checkout Session, amount converted to minor units
- `CreateRecurringDonation` - subscription-mode Checkout Session using the price plan as a quantity multiplier
- `UserService` - Statamic User facade wrapper: find by email, create with random password, update with stripe_customer_id

### Configuration
Published to `config/donation-checkout.php`:
- Stripe keys (via env vars `STRIPE_SECRET_KEY`, `STRIPE_PUBLISHABLE_KEY`)
- `stripe_price_plan_id` - Required for recurring donations (create £1/month product in Stripe, use price ID)
- Success/cancel URLs for both donation types
- Currency (default: gbp)
- `create_users` - whether anonymous donors get a Statamic user record (default true)
- `rate_limit_per_minute` / `rate_limit_per_day` - per-IP limits on the donation endpoint

### Route
Single route: `POST /donation-checkout/start`, in the `web` group so CSRF applies. Rate limited via
the named `donation-checkout` limiter registered in `ServiceProvider::registerRateLimiter()`.

## Key Implementation Details

- Recurring donations use quantity-based pricing: amount becomes quantity × £1 price plan
- Users are created with random 16-char passwords (donation flow doesn't require login)
- `stripe_customer_id` stored on Statamic user for reuse
- The endpoint is public and unauthenticated; treat all input as hostile and keep Stripe error
  detail server-side (logged) rather than in the response body
