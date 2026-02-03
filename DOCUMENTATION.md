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
Installing Donation Checkout will add a `donation-checkout.php` folder to your `config` folder. You will need to add
`stripe_price_plan_id` and decide on the success urls for single and recurring donations.

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

Add the form to your template:

```antlers
{{ donation:form }}
```

Add the JavaScript before your closing `</body>` tag:

```antlers
{{ donation:scripts }}
```

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

### Additional Tags

| Tag | Description |
|-----|-------------|
| `{{ donation:stripe_key }}` | Outputs your Stripe publishable key |
| `{{ donation:endpoint }}` | Outputs the API endpoint URL |
| `{{ donation:currency }}` | Outputs the configured currency code |

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
