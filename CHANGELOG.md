# Changelog

All notable changes to `donation-checkout` will be documented in this file.

## 3.0.0 - 2026-06-17

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

## v2.1.4 - 2026-05-27

### Fixed

- Updated `statamic/cms` (6.14.0 to 6.20.0) to resolve server-side request forgery via Glide ([CVE-2026-45660](https://github.com/advisories/GHSA-pf9c-ch8r-2958)) and email enumeration via forgot password endpoint ([CVE-2026-44306](https://github.com/advisories/GHSA-m24v-f7g5-gq67)).
- Updated `symfony/http-kernel` (7.4.8 to 7.4.12) to resolve HEAD request bypass on `#[IsGranted]` / `#[IsSignatureValid]` / `#[IsCsrfTokenValid]` method filters ([CVE-2026-45075](https://symfony.com/cve-2026-45075)).
- Updated `symfony/mailer` (7.4.8 to 7.4.12) to resolve argument injection in SendmailTransport via dash-prefixed recipient address ([CVE-2026-45068](https://symfony.com/cve-2026-45068)).
- Updated `symfony/mime` (7.4.8 to 7.4.12) to resolve header injection via non-printable characters ([CVE-2026-45069](https://symfony.com/cve-2026-45069)).
- Updated `symfony/yaml` (7.4.8 to 7.4.12) to resolve exponential memory allocation via recursive alias expansion ([CVE-2026-45304](https://symfony.com/cve-2026-45304)), ReDoS via catastrophic backtracking in `Parser::cleanup()` ([CVE-2026-45305](https://symfony.com/cve-2026-45305)), and stack exhaustion via unbounded recursion ([CVE-2026-45133](https://symfony.com/cve-2026-45133)).

## v2.1.3 - 2026-05-06

### Fixed

- Updated `webonyx/graphql-php` (15.32.0 to 15.32.3) to resolve unbounded recursion in parser causing stack overflow on crafted nested input ([GHSA-r7cg-qjjm-xhqq](https://github.com/advisories/GHSA-r7cg-qjjm-xhqq)) and quadratic validation cost in `OverlappingFieldsCanBeMerged` via inline fragments ([GHSA-fc86-6rv6-2jpm](https://github.com/advisories/GHSA-fc86-6rv6-2jpm)).

## v2.1.2 - 2026-04-20

### Fixed

- Updated `statamic/cms` (6.8.0 to 6.14.0) to resolve unsafe method invocation via query value resolution allowing data destruction ([GHSA-4jjr-vmv7-wh4w](https://github.com/advisories/GHSA-4jjr-vmv7-wh4w)).
- Updated `rhukster/dom-sanitizer` (1.0.8 to 1.0.10) to resolve SVG style tag CSS injection via unfiltered `url()` and `@import` directives ([CVE-2026-40301](https://github.com/advisories/GHSA-93vf-569f-22cq)).
- Updated `webonyx/graphql-php` to resolve denial of service via quadratic complexity in OverlappingFieldsCanBeMerged validation ([CVE-2026-40476](https://github.com/advisories/GHSA-68jq-c3rv-pcrr)).

## Release 2.1.1 - 2026-03-30

### Fixed

- Updated `statamic/cms` (6.7.0 to 6.8.0) to resolve 8 security vulnerabilities including a critical account takeover via password reset link injection (CVE-2026-27593) and remote code execution via Antlers inputs (CVE-2026-28425).
- Updated `league/commonmark` (2.8.1 to 2.8.2) to resolve embed extension allowed_domains bypass (CVE-2026-33347).

## 2.1.1 - 2026-03-31

### Fixed

- Updated `statamic/cms` (6.7.0 to 6.8.0) to resolve 8 security vulnerabilities including a critical account takeover via password reset link injection (CVE-2026-27593) and remote code execution via Antlers inputs (CVE-2026-28425).
- Updated `league/commonmark` (2.8.1 to 2.8.2) to resolve embed extension allowed_domains bypass (CVE-2026-33347).

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
