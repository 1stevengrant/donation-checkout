# Changelog

All notable changes to `donation-checkout` will be documented in this file.

## 2.1.0

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
