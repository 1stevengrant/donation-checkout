# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Statamic addon that integrates Stripe Checkout for handling variable single and recurring donations. It includes a CP dashboard for managing donations, a donor portal with magic link authentication, Stripe webhook integration, and configurable Gift Aid support. It uses the Stripe PHP SDK and extends Statamic's AddonServiceProvider.

## Commands

```bash
# Run tests
composer test

# Run tests with coverage
composer test-coverage

# Format code with Laravel Pint
composer format

# Build JS assets for CP
npm run build

# Register Stripe webhook endpoint
php artisan donation-checkout:setup-webhook
```

## Architecture

### Entry Point
- `ServiceProvider.php` extends `Statamic\Providers\AddonServiceProvider` and registers routes (web + CP), tags, views, widgets, Vite assets, nav items, permissions, artisan commands, and auto-installs the `donation_messages` Global Set.

### Statamic Tags
`src/Tags/Donation.php` provides template tags:
- `{{ donation:form }}` renders the donation form with vanilla JS. Supports custom metadata fields (Gift Aid, etc.) configured via the CP Global Set.
- `{{ donation:scripts }}` outputs the vanilla JS handler (place before closing `</body>`)
- `{{ donation:styles }}` outputs default CSS (toggle switches, form fields, magic link form, portal link)
- `{{ donation:stripe_key }}` outputs the Stripe publishable key
- `{{ donation:endpoint }}` outputs the donation API endpoint URL
- `{{ donation:currency }}` outputs the configured currency code
- `{{ donation:thank_you }}` tag pair returning session details + global set messages for thank you pages
- `{{ donation:magic_link_form }}` renders magic link email form (hidden when logged in, shows portal link instead)
- `{{ donation:portal }}` tag pair returning donation/subscription data for authenticated donors
- `{{ donation:portal_cancel_url }}` returns cancel endpoint URL for use in forms

Form tag parameters: `amounts`, `default`, `frequency`, `currency_symbol`, `button_text`

### Views
`resources/views/` (publishable Blade templates):
- `form.blade.php` with custom field support (toggle switches for checkboxes)
- `scripts.blade.php` with custom field collection in JS submission
- `styles.blade.php` with default CSS including toggle switch, magic link form, portal link styles
- `thank-you.blade.php` for the thank you page
- `portal.blade.php` for the donor self-service portal
- `emails/magic-link.blade.php`, `emails/subscription-paused.blade.php`, `emails/subscription-resumed.blade.php`
- `widgets/donation-stats.blade.php` for the CP dashboard widget

### CP (Vue/Inertia)
`resources/js/` contains Vue components registered as Inertia pages via `Statamic.$inertia.register()`:
- `pages/DonationsIndex.vue` uses `<ui-listing>` with client-side sort/search, `<ui-badge>` for status, `<ui-confirmation-modal>` for actions
- `pages/DonorProfile.vue` shows donor detail with separate subscription/donation listings

Built with Vite, compiled assets in `resources/dist/build/`. Uses Statamic's globally registered `ui-*` components. Vue externals plugin maps `vue` to `window.Vue`.

CP controllers extend `Statamic\Http\Controllers\CP\CpController` and return `Inertia::render()`. Each action (cancel, pause, resume, refund) has its own invokable controller using standard REST verbs:
- `DELETE /subscriptions/{id}` (cancel)
- `PUT /subscriptions/{id}/pause` (pause)
- `PUT /subscriptions/{id}/resume` (resume)
- `POST /payments/{id}/refund` (refund)

### Request Flow
1. POST to `/donation-checkout/start` hits `StartDonationController` (invokable)
2. `DonationRequest` validates: amount, email, first_name, last_name, frequency (single|recurring), plus dynamic custom fields from config
3. `UserService` finds or creates Statamic user by email
4. `CreateStripeCustomer` creates Stripe customer if needed
5. `CreateSingleDonation` or `CreateRecurringDonation` creates Checkout Session with metadata and optional billing address collection
6. Returns Stripe Checkout Session URL (frontend redirects)

### Actions
All follow the invokable pattern with constructor-promoted `private readonly StripeClient $stripe`:
- `CreateStripeCustomer`, `CreateSingleDonation`, `CreateRecurringDonation` (donation flow)
- `CancelSubscription`, `PauseSubscription`, `ResumeSubscription` (subscription management)
- `RefundPayment` (refunds)
- `ListDonations` (with `latest_charge` expansion for refund status), `ListSubscriptions` (pagination support)
- `RetrieveCheckoutSession` (with `line_items` expansion)
- `SendMagicLink` (generates temporary signed URL, dispatches mail)

### Services
- `UserService` wraps Statamic User facade: `findByEmail`, `findByStripeCustomerId`, `createUser`, `updateUser`

### Concerns (Traits)
- `ClearsStripeCache` clears per-customer and widget caches
- `ResolvesPaymentStatus` derives effective status from `latest_charge.refunded`
- `SendsDonorNotifications` sends pause/resume emails with 60-second cache lock to prevent duplicates

### Settings
`src/Support/Settings.php` reads from the `donation_messages` Global Set first, falling back to config. Gracefully handles missing Statamic bindings (tests). Methods: `collectBillingAddress()`, `giftAidEnabled()`, `giftAidLabel()`, `donorPortalEnabled()`, `donorCanCancel()`, `customFields()`.

### Webhook
- `POST /donation-checkout/webhook/stripe` registered directly in ServiceProvider (outside web middleware group, no CSRF)
- `VerifyStripeWebhook` middleware verifies signatures via `Stripe\Webhook::constructEvent()`
- `StripeWebhookController` handles 6 event types, clears caches, dispatches Laravel events, sends notification emails

### Events
All in `src/Events/` with `public readonly` constructor-promoted properties:
- `DonationCompleted` (stripeCustomerId, sessionId, mode, amountInCents, currency)
- `SubscriptionPaused`, `SubscriptionResumed`, `SubscriptionCancelled` (stripeCustomerId, subscriptionId)
- `SubscriptionUpdated` (stripeCustomerId, subscriptionId, data)
- `DonationRefunded` (stripeCustomerId, chargeId, amountRefundedInCents)
- `RecurringPaymentFailed` (stripeCustomerId, invoiceId, subscriptionId)
- `RecurringPaymentSucceeded` (stripeCustomerId, invoiceId, amountInCents)

### Configuration
Published to `config/donation-checkout.php`:
- Stripe keys (`STRIPE_SECRET_KEY`, `STRIPE_PUBLISHABLE_KEY`, `STRIPE_WEBHOOK_SECRET`)
- `stripe_price_plan_id` for recurring donations (quantity x £1 price plan)
- Success/cancel URLs (relative paths auto-resolved to absolute at runtime)
- Currency (default: gbp)
- `magic_link_expiry_hours`, `donor_portal_enabled`, `donor_can_cancel_subscriptions`
- `collect_billing_address` for Gift Aid/HMRC compliance
- `custom_fields` array for additional metadata fields

Settings tab in the `donation_messages` Global Set provides CP control over: billing address collection, Gift Aid toggle + label, donor portal, donor cancellation.

### Routes
- **Web**: `POST /start`, `GET /thank-you`, `POST /magic-link`, `GET /magic-link/verify` (signed), `GET /portal` (auth), `POST /portal/subscriptions/{id}/cancel` (auth)
- **Webhook**: `POST /webhook/stripe` (registered in ServiceProvider, VerifyStripeWebhook middleware)
- **CP**: `GET /`, `GET /donor/{email}`, `DELETE /subscriptions/{id}`, `PUT /subscriptions/{id}/pause`, `PUT /subscriptions/{id}/resume`, `POST /payments/{id}/refund`

## Key Implementation Details

- Recurring donations use quantity-based pricing: amount becomes quantity x £1 price plan
- Users are created with random 16-char passwords (donation flow doesn't require login)
- `stripe_customer_id` stored on Statamic user for reuse
- Stripe is source of truth (no local database). CP listing caches Stripe responses for 2 minutes
- CP views use Vue/Inertia with Statamic's `@ui` component library (Listing, Badge, Header, ConfirmationModal, etc.)
- Webhook route bypasses CSRF by being registered outside the web middleware group
- Settings are read from Global Set first, falling back to config, with graceful handling when Statamic bindings are unavailable
- Gift Aid checkbox renders as a styled toggle switch in the donation form
- Name resolution falls back from first_name + last_name to name to email
- Refund status derived from `latest_charge.refunded` on PaymentIntent (Stripe keeps status as `succeeded` after refund)

# currentDate
Today's date is 2026-03-18.
