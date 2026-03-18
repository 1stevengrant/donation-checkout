# Changelog

All notable changes to `donation-checkout` will be documented in this file.

## 3.0.0 - 2026-03-18

### Control Panel

- **Donations Dashboard** accessible under Tools > Donations. Sortable, searchable listing of all single and recurring donations with status badges (Active, Paused, Cancelled, Succeeded, Refunded). Click any donor's name to view their full profile.
- **Donor Profile** showing a donor's complete history split into recurring and single donation tables, with metadata display.
- **Subscription Actions** with confirmation dialogs: pause, resume, and cancel subscriptions directly from the CP.
- **Refund Payments** with confirmation dialog from the CP.
- **Dashboard Widget** showing total raised, active subscriptions count, and 5 most recent donations. Add it via CP > Dashboard > Widgets.
- **Permissions**: `view donations` (see the listing), `cancel donations` (pause/resume/cancel subscriptions), `refund donations` (issue refunds). Configured under Users > Roles.

### Gift Aid (UK)

- **Gift Aid Toggle** on the donation form. Enable it from the CP under Globals > Donation Messages > Settings. Renders as a styled toggle switch with a customisable label (default: "Boost your donation by 25% with Gift Aid").
- **Billing Address Collection** toggle in the same Settings tab. When enabled, Stripe Checkout requires the donor's full billing address (needed for HMRC Gift Aid claims).
- Gift Aid declaration is stored as `gift_aid: yes/no` in Stripe session metadata, visible in both the CP and Stripe Dashboard.

### Thank You Page

- Customisable thank you page at `/donation-checkout/thank-you`. Different headings, messages, and CTA buttons for single vs recurring donations.
- Edit the copy from the CP under Globals > Donation Messages (Single Donations and Recurring Donations tabs).
- Also available as a `{{ donation:thank_you }}` tag pair for building custom thank you templates.
- Success URLs in config now accept relative paths (e.g. `/donation-checkout/thank-you?session_id={CHECKOUT_SESSION_ID}`) and are resolved to absolute URLs automatically.

### Donor Emails

- **Subscription Paused Email** thanking the donor for their support when their recurring donation is paused.
- **Subscription Resumed Email** confirming their donation has been reactivated.
- **Magic Link Email** with a time-limited signed link to access the donor portal.
- Emails are sent from both CP actions and Stripe webhooks, with duplicate prevention (60-second cache lock).

### Donor Portal

- **Magic Link Authentication**: donors enter their email on the frontend to receive a signed login link (no password needed). The `{{ donation:magic_link_form }}` tag renders the form, and automatically shows a "Manage your donations" link instead when the donor is already logged in.
- **Self-Service Portal** at `/donation-checkout/portal` showing the donor's recurring and single donations with status badges, dates, and amounts.
- **Self-Cancellation**: donors can cancel their own subscriptions from the portal (configurable via Settings toggle in the CP).

### Stripe Webhooks

- Webhook endpoint at `POST /donation-checkout/webhook/stripe` with HMAC signature verification.
- Handles 6 events: `checkout.session.completed`, `customer.subscription.updated`, `customer.subscription.deleted`, `charge.refunded`, `invoice.payment_failed`, `invoice.payment_succeeded`.
- Clears CP caches and dispatches Laravel events (`DonationCompleted`, `SubscriptionPaused`, `SubscriptionResumed`, `SubscriptionCancelled`, `SubscriptionUpdated`, `DonationRefunded`, `RecurringPaymentFailed`, `RecurringPaymentSucceeded`) so your application can react to donation lifecycle changes.
- **Setup Command**: `php artisan donation-checkout:setup-webhook` registers the endpoint in Stripe via the API and writes `STRIPE_WEBHOOK_SECRET` to your `.env` file automatically.

### Custom Metadata Fields

- Configure additional form fields (text inputs, checkboxes) in `config/donation-checkout.php` under `custom_fields`. They are rendered on the form, validated, and passed to Stripe session metadata.
- Checkbox fields render as styled toggle switches.
- Metadata is visible in the CP donations listing and donor profile.

### CP Settings (Global Set)

- All key settings are now configurable from the CP under Globals > Donation Messages > Settings: billing address collection, Gift Aid (enable/disable + label), donor portal, and donor self-cancellation.
- Settings take priority over config file values.

### Technical

- CP views built with Vue/Inertia using Statamic's `@ui` component library (Listing, Badge, Header, ConfirmationModal, Heading, DropdownItem).
- Each CP action is its own invokable controller with standard REST verbs: `DELETE` (cancel), `PUT` (pause, resume), `POST` (refund).
- Refund status detected via `latest_charge.refunded` on Stripe PaymentIntents.
- Per-customer Stripe API response caching (2 minutes) with automatic invalidation on actions and webhooks.
- 62 tests covering actions, controllers, webhooks, thank you page, magic link flow, and portal authentication.

## 2.1.0 - 2026-03-18

### Breaking Changes

- Removed `PaymentService` class. Replaced with single-action classes: `CreateStripeCustomer`, `CreateSingleDonation`, `CreateRecurringDonation`.
- Re-enabled CSRF protection on the donation endpoint. Ensure your layout includes `<meta name="csrf-token" content="{{ csrf_token }}">`.
- Amount validation now requires integers. Decimal values (e.g. 10.5) are rejected.
- Frequency validation now only accepts `single` or `recurring`.

### Fixed

- `UserService::updateUser` now merges data instead of replacing all user data. Previously, updating `stripe_customer_id` would wipe `first_name` and `last_name`.
- Config publishing now works correctly. The `donation-checkout-config` publish tag was referenced but never registered.

### Added

- `{{ donation:styles }}` tag for optional default CSS styles.
- Rate limiting (10 requests/minute) on the donation endpoint.
- `StripeClient` instance injection via service container (replaces static `Stripe::setApiKey`).
- Hidden frequency input and `max` attribute on amount input for progressive enhancement.
- Full test suite with 34 tests covering validation, actions, services, and controller integration.
- Orchestra Testbench for Laravel testing support.
- Rector configuration for automated code quality improvements.
- Pint configuration with Laravel preset.

### Removed

- Dead `getCustomer` method from the old `PaymentService`.
- CSRF middleware exclusion (no longer needed).
