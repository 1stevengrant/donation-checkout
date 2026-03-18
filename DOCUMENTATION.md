## Documentation

### Stripe Configuration
Donation Checkout can handle both single and recurring donations. It uses Stripe Checkout to handle the payment process.

If you wish to create recurring donations, you will need to create a product in Stripe. You can do this by going to
the [Stripe dashboard and clicking on Products](https://dashboard.stripe.com/test/products).

You can give the product any name you like - I use `Donation` personally. The product name will be vieweable in the
Stripe Checkout though, so make sure it's sensible. The price should be 1.00, as this is the price that will be used
as the multiplier for the donation amount and the billing period should be set as `Monthly`.

After you've created the product, you will need to copy the price ID. You can find this by clicking on the product
and seeing the pricing section.

![](https://d.pr/i/fZ82pT+)

### Statamic Configuration

You will need to add the following to your `.env` file:

```dotenv
STRIPE_SECRET_KEY=
STRIPE_PUBLISHABLE_KEY=
```

Installing Donation Checkout will add a `donation-checkout.php` file to your `config` folder. You will need to add
`stripe_price_plan_id` and decide on the success URLs for single and recurring donations. Success and cancel URLs can be
relative paths (e.g. `/donation-checkout/thank-you?session_id={CHECKOUT_SESSION_ID}`) and will be resolved to absolute
URLs automatically.

### Frontend Implementation

There are two ways to implement the donation form in your Statamic site:

1. **Using Statamic Tags** (Recommended) - Quick setup with customisable views
2. **Custom Implementation** - Full control using the API endpoint directly

Both methods require the following parameters to be passed to Stripe:
- amount
- first name
- last name
- email address
- frequency (single or recurring)

---

## Option 1: Using Statamic Tags

The addon provides Antlers tags for quick implementation using vanilla JavaScript (no framework dependencies).

### Basic Usage

Add the CSRF meta tag to your layout's `<head>`:

```antlers
<meta name="csrf-token" content="{{ csrf_token }}">
```

Add the form and scripts to your template:

```antlers
{{ donation:form }}
{{ donation:scripts }}
```

### Default Styles (Optional)

The form ships unstyled by default. To include the built-in styles, add the styles tag to your template (ideally before the form):

```antlers
{{ donation:styles }}
{{ donation:form }}
{{ donation:scripts }}
```

This outputs a `<style>` block with sensible defaults. If you prefer to write your own CSS, simply omit the tag and target the `.donation-*` classes listed below.

### Customising the Form

The `donation:form` tag accepts several parameters:

| Parameter | Default | Description |
|-----------|---------|-------------|
| `amounts` | `5\|10\|50` | Pipe-separated preset amounts |
| `default` | `10` | Default selected amount |
| `frequency` | `recurring` | Default frequency (`single` or `recurring`) |
| `currency_symbol` | `£` | Currency symbol to display |
| `button_text` | `Donate` | Submit button text |

Example with custom options:

```antlers
{{ donation:form amounts="10|25|50|100" default="25" frequency="single" currency_symbol="$" button_text="Give Now" }}
```

### Publishing Views for Customisation

To fully customise the form markup, publish the views to your project:

```bash
php artisan vendor:publish --tag=donation-checkout-views
```

This will copy the views to `resources/views/vendor/donation-checkout/`:

- `form.blade.php` - The donation form markup
- `scripts.blade.php` - The vanilla JavaScript component
- `styles.blade.php` - The default CSS styles

You can then edit these files to match your site's design. The form uses CSS classes prefixed with `donation-` for easy styling:

- `.donation-form` - Form container
- `.donation-frequency` - Frequency button group
- `.donation-frequency-btn` - Frequency buttons (has `.active` state)
- `.donation-amounts` - Amount button container
- `.donation-amount-btn` - Amount buttons (has `.active` state)
- `.donation-custom-amount` - Custom amount input wrapper
- `.donation-fields` - Name fields container
- `.donation-field` - Individual field wrapper
- `.donation-submit` - Submit button container
- `.donation-submit-btn` - Submit button
- `.donation-error` - Error message display
- `.donation-toggle` - Toggle switch container (for checkbox custom fields)
- `.donation-toggle-track` - Toggle switch track
- `.donation-toggle-thumb` - Toggle switch thumb
- `.donation-toggle-label` - Toggle switch label text
- `.donation-magic-link` - Magic link form container
- `.donation-magic-link-heading` - Magic link heading text
- `.donation-portal-link` - Portal link (shown when logged in)

### Additional Tags

| Tag | Description |
|-----|-------------|
| `{{ donation:styles }}` | Outputs default CSS styles (optional) |
| `{{ donation:stripe_key }}` | Outputs your Stripe publishable key |
| `{{ donation:endpoint }}` | Outputs the API endpoint URL |
| `{{ donation:currency }}` | Outputs the configured currency code |
| `{{ donation:thank_you }}` | Tag pair with thank you page data (heading, message, amount, etc.) |
| `{{ donation:magic_link_form }}` | Magic link email form (shows portal link when logged in) |
| `{{ donation:portal }}` | Tag pair with donor's donations and subscriptions |
| `{{ donation:portal_cancel_url :id="sub_id" }}` | Cancel URL for a subscription |

---

## Option 2: Custom Implementation

For full control, you can build your own form and interact with the API endpoint directly.

### Expected Outcomes

Passing the correct parameters to the `/donation-checkout/start` endpoint will check for a Statamic user with the
submitted email address. If one doesn't exist, it will create one. It will then check for Stripe Customer ID against that user. If one doesn't exist, it will create one.

From there it will create the appropriate Stripe Checkout session and return the session ID to the frontend.

### Plain JavaScript Example

Here's a framework-agnostic implementation using vanilla JavaScript:

```html
<head>
    <meta name="csrf-token" content="{{ csrf_token }}">
</head>
<body>
    <form id="donation-form">
        <div>
            <button type="button" class="frequency-btn active" data-frequency="recurring">Monthly</button>
            <button type="button" class="frequency-btn" data-frequency="single">One-off</button>
        </div>

        <p>Choose a donation amount</p>

        <div id="amount-buttons">
            <button type="button" class="amount-btn" data-amount="5">£5</button>
            <button type="button" class="amount-btn active" data-amount="10">£10</button>
            <button type="button" class="amount-btn" data-amount="50">£50</button>
        </div>

        <label>
            <span>£</span>
            <input type="number" name="amount" id="amount" value="10" min="1" required>
        </label>

        <div>
            <label for="first_name">First Name</label>
            <input type="text" name="first_name" id="first_name" required>
        </div>

        <div>
            <label for="last_name">Last Name</label>
            <input type="text" name="last_name" id="last_name" required>
        </div>

        <div>
            <label for="email">Email</label>
            <input type="email" name="email" id="email" required>
        </div>

        <button type="submit" id="submit-btn">Donate</button>
        <p id="error-message" style="display: none; color: red;"></p>
    </form>

    <script>
        (function() {
            const form = document.getElementById('donation-form');
            const amountInput = document.getElementById('amount');
            const submitBtn = document.getElementById('submit-btn');
            const errorMessage = document.getElementById('error-message');
            let frequency = 'recurring';

            // Frequency toggle
            document.querySelectorAll('.frequency-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.frequency-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    frequency = this.dataset.frequency;
                });
            });

            // Amount buttons
            document.querySelectorAll('.amount-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.amount-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    amountInput.value = this.dataset.amount;
                });
            });

            // Update active state when typing custom amount
            amountInput.addEventListener('input', function() {
                document.querySelectorAll('.amount-btn').forEach(b => b.classList.remove('active'));
            });

            // Form submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                submitBtn.disabled = true;
                submitBtn.textContent = 'Processing...';
                errorMessage.style.display = 'none';

                const data = {
                    amount: parseInt(amountInput.value),
                    frequency: frequency,
                    first_name: document.getElementById('first_name').value,
                    last_name: document.getElementById('last_name').value,
                    email: document.getElementById('email').value
                };

                fetch('/donation-checkout/start', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(data)
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => Promise.reject(err));
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.url) {
                        window.location.href = data.url;
                    }
                })
                .catch(error => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Donate';
                    errorMessage.textContent = error.message || 'An error occurred. Please try again.';
                    errorMessage.style.display = 'block';
                });
            });
        })();
    </script>
</body>
```

### Alpine.js Example

If you prefer using Alpine.js for reactivity:

```html
<head>
    <meta name="csrf-token" content="{{ csrf_token }}">
</head>
<body>
    <div x-data="donateForm">
        <form @submit.prevent="makeDonation()" method="post">
            <div>
                <button type="button"
                        :class="{ 'active': frequency === 'recurring' }"
                        @click="frequency = 'recurring'">
                    Monthly
                </button>
                <button type="button"
                        :class="{ 'active': frequency === 'single' }"
                        @click="frequency = 'single'">
                    One-off
                </button>
            </div>

            <p>Choose a donation amount</p>

            <div>
                <template x-for="a in amounts">
                    <button type="button"
                            x-text="`£${a}`"
                            :class="{ 'active': amount === a }"
                            @click="amount = a">
                    </button>
                </template>
            </div>

            <label>
                <span>£</span>
                <input type="number" name="amount" x-model="amount" min="1" required>
            </label>

            <div>
                <label for="first_name">First Name</label>
                <input type="text" name="first_name" x-model="first_name" required>
            </div>

            <div>
                <label for="last_name">Last Name</label>
                <input type="text" name="last_name" x-model="last_name" required>
            </div>

            <div>
                <label for="email">Email</label>
                <input type="email" name="email" x-model="email" required>
            </div>

            <button type="submit" :disabled="loading">
                <span x-show="!loading">Donate</span>
                <span x-show="loading">Processing...</span>
            </button>

            <p x-show="error" x-text="error" style="color: red;"></p>
        </form>
    </div>

    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('donateForm', () => ({
                amount: 10,
                amounts: [5, 10, 50],
                frequency: 'recurring',
                first_name: '',
                last_name: '',
                email: '',
                loading: false,
                error: null,

                makeDonation() {
                    this.loading = true;
                    this.error = null;

                    fetch('/donation-checkout/start', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            amount: this.amount,
                            frequency: this.frequency,
                            first_name: this.first_name,
                            last_name: this.last_name,
                            email: this.email
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => Promise.reject(err));
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.url) {
                            window.location.href = data.url;
                        }
                    })
                    .catch(error => {
                        this.loading = false;
                        this.error = error.message || 'An error occurred. Please try again.';
                    });
                }
            }));
        });
    </script>
</body>
```

### How It Works

Both examples handle:

1. **Frequency toggle** - Switch between `recurring` (monthly) and `single` (one-off) donations
2. **Preset amounts** - Quick selection buttons for common donation amounts
3. **Custom amount** - Manual input for any donation amount
4. **Form validation** - Required fields for first name, last name, and email
5. **CSRF protection** - Token sent with the request to prevent cross-site attacks
6. **Error handling** - Display errors if the request fails
7. **Loading state** - Disable the button and show feedback while processing

On successful submission, the endpoint returns a Stripe Checkout URL and the user is redirected to complete their payment.

---

## Thank You Page

After a successful donation, donors can be redirected to a built-in thank you page. Set your success URLs in `config/donation-checkout.php`:

```php
'single_donation_success_url' => '/donation-checkout/thank-you?session_id={CHECKOUT_SESSION_ID}',
'recurring_donation_success_url' => '/donation-checkout/thank-you?session_id={CHECKOUT_SESSION_ID}',
```

The thank you page displays a customisable heading, message, and call-to-action button. Edit the copy from the CP under **Globals > Donation Messages** (separate tabs for Single Donations and Recurring Donations).

If you prefer to build your own thank you template, use the `{{ donation:thank_you }}` tag pair:

```antlers
{{ donation:thank_you }}
    <h1>{{ heading }}</h1>
    <p>{{ currency | upper }} {{ amount }}</p>
    <p>{{ message }}</p>
    <a href="{{ cta_url }}">{{ cta_text }}</a>
{{ /donation:thank_you }}
```

To publish the built-in thank you view for customisation:

```bash
php artisan vendor:publish --tag=donation-checkout-views
```

---

## Gift Aid (UK)

Gift Aid allows UK charities to claim an extra 25% on donations from UK taxpayers. Donation Checkout supports this with a toggle switch on the donation form and billing address collection for HMRC compliance.

### Enabling Gift Aid

1. Go to **CP > Globals > Donation Messages > Settings**
2. Enable **Gift Aid** (toggle switch)
3. Optionally customise the label (default: "Boost your donation by 25% with Gift Aid")
4. Enable **Collect Billing Address** (Stripe Checkout will require the donor's full address)

When enabled, the donation form displays a styled toggle switch. The donor's declaration is stored as `gift_aid: yes/no` in the Stripe session metadata, visible in both the CP donations listing and the Stripe Dashboard.

### Custom Metadata Fields

You can add additional fields beyond Gift Aid in `config/donation-checkout.php`:

```php
'custom_fields' => [
    'message' => ['type' => 'text', 'label' => 'Leave a message'],
    'newsletter' => ['type' => 'checkbox', 'label' => 'Subscribe to our newsletter'],
],
```

Checkbox fields render as toggle switches. Text fields render as standard text inputs. All custom field values are passed to Stripe as session metadata.

---

## Donor Portal

The donor portal gives donors a self-service view of their donation history without needing a password.

### Magic Link Authentication

Add the magic link form to your template:

```antlers
{{ donation:magic_link_form }}
```

This renders an email input form. When submitted, the donor receives an email with a time-limited signed link. Clicking the link logs them in and redirects to the portal at `/donation-checkout/portal`.

When the donor is already logged in, the tag renders a "Manage your donations" link to the portal instead.

You can customise the tag:

```antlers
{{ donation:magic_link_form button_text="Access my donations" heading="Returning donor?" portal_text="View your history" }}
```

### Portal Page

The portal at `/donation-checkout/portal` shows:

- Recurring donations with status (Active, Paused, Cancelled), amount, start date, and next payment date
- Single donations with status (Succeeded, Refunded), amount, and date
- Cancel button for active subscriptions (configurable via CP Settings)

The portal view is publishable:

```bash
php artisan vendor:publish --tag=donation-checkout-views
```

### Antlers Tags for Custom Portals

If you prefer to build your own portal template, use the `{{ donation:portal }}` tag pair:

```antlers
{{ donation:portal }}
    {{ if authenticated }}
        {{ subscriptions }}
            <p>{{ currency | upper }} {{ amount }}/mo ({{ status }})</p>
            {{ if can_cancel }}
                <form method="POST" action="{{ cancel_url }}">
                    {{ csrf_field }}
                    <button type="submit">Cancel</button>
                </form>
            {{ /if }}
        {{ /subscriptions }}

        {{ donations }}
            <p>{{ currency | upper }} {{ amount }} on {{ date }} ({{ status }})</p>
        {{ /donations }}
    {{ else }}
        <p>Please log in to view your donations.</p>
    {{ /if }}
{{ /donation:portal }}
```

### Configuration

The following portal settings can be changed from the CP under **Globals > Donation Messages > Settings**:

- **Enable Donor Portal** (show/hide the magic link form)
- **Donors Can Cancel** (allow donors to cancel their own subscriptions)

The magic link expiry is configurable in `config/donation-checkout.php`:

```php
'magic_link_expiry_hours' => 24,
```

---

## Stripe Webhooks

Webhooks keep your site in sync when events happen outside your application (e.g. a subscription is cancelled from the Stripe Dashboard, or a payment fails).

### Setup

The easiest way to register the webhook endpoint in Stripe is with the artisan command:

```bash
php artisan donation-checkout:setup-webhook
```

This creates the webhook endpoint in Stripe via the API and writes the `STRIPE_WEBHOOK_SECRET` to your `.env` file automatically.

For custom URLs (e.g. when using ngrok for local development):

```bash
php artisan donation-checkout:setup-webhook --url=https://your-tunnel.ngrok.io/donation-checkout/webhook/stripe
```

To update the events on an existing webhook:

```bash
php artisan donation-checkout:setup-webhook --update
```

### Events Handled

| Stripe Event | What Happens |
|---|---|
| `checkout.session.completed` | Clears caches, dispatches `DonationCompleted` event |
| `customer.subscription.updated` | Detects pause/resume, sends donor email, dispatches `SubscriptionPaused`/`SubscriptionResumed`/`SubscriptionUpdated` |
| `customer.subscription.deleted` | Clears caches, dispatches `SubscriptionCancelled` |
| `charge.refunded` | Clears caches, dispatches `DonationRefunded` |
| `invoice.payment_failed` | Clears caches, dispatches `RecurringPaymentFailed` |
| `invoice.payment_succeeded` | Clears caches, dispatches `RecurringPaymentSucceeded` |

### Listening to Events

All webhook events dispatch Laravel events that your application can listen to:

```php
// In a service provider or EventServiceProvider
use Ghijk\DonationCheckout\Events\DonationCompleted;
use Ghijk\DonationCheckout\Events\SubscriptionCancelled;
use Ghijk\DonationCheckout\Events\RecurringPaymentFailed;

Event::listen(DonationCompleted::class, function ($event) {
    // $event->stripeCustomerId
    // $event->sessionId
    // $event->mode ('payment' or 'subscription')
    // $event->amountInCents
    // $event->currency
});

Event::listen(RecurringPaymentFailed::class, function ($event) {
    // Send a dunning email, notify admin, etc.
    // $event->stripeCustomerId
    // $event->invoiceId
    // $event->subscriptionId
});
```

### Manual Setup

If you prefer to configure the webhook manually in the Stripe Dashboard:

1. Go to [Developers > Webhooks](https://dashboard.stripe.com/webhooks)
2. Add endpoint: `https://yoursite.com/donation-checkout/webhook/stripe`
3. Select events: `checkout.session.completed`, `customer.subscription.updated`, `customer.subscription.deleted`, `charge.refunded`, `invoice.payment_failed`, `invoice.payment_succeeded`
4. Copy the signing secret and add to `.env`:

```dotenv
STRIPE_WEBHOOK_SECRET=whsec_...
```

---

## Control Panel

### Donations Dashboard

Navigate to **Tools > Donations** in the CP to see all donations and subscriptions. The listing supports sorting by date, donor, type, amount, and status. Use the search bar to find specific donors.

Click a donor's name to view their full profile with separate tables for recurring and single donations.

### Actions

From the donations listing or donor profile, you can:

- **Pause** a recurring donation (invoices are voided while paused)
- **Resume** a paused donation
- **Cancel** a recurring donation (cannot be undone)
- **Refund** a single donation (full refund, cannot be undone)

All actions require confirmation and are permission-gated.

### Permissions

Configure under **Users > Roles**:

- `view donations` allows seeing the donations listing and donor profiles
- `cancel donations` allows pausing, resuming, and cancelling subscriptions
- `refund donations` allows refunding single donations

### Dashboard Widget

Add the Donation Stats widget to your dashboard via **CP > Dashboard**. It shows total raised, active subscription count, and the 5 most recent donations. Data is cached for 5 minutes by default.

### Donor Emails

When a subscription is paused or resumed (from the CP or via Stripe webhook), the donor receives an email thanking them for their support. Email templates are publishable:

```bash
php artisan vendor:publish --tag=donation-checkout-views
```

Templates are in `resources/views/vendor/donation-checkout/emails/`:

- `subscription-paused.blade.php`
- `subscription-resumed.blade.php`
- `magic-link.blade.php`
