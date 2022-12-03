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

A donation requires a few parametersto be passed to Stripe:
- amount
- first name
- last name
- email address
- frequency (single or recurring)

### Expected outcomes

Passing the correct parameters to the `/donation-checkout/start` endpoint will check for a Statamic user with the
submitted email address. If one doesn't exist, it will create one. It will then check for Stripe Customer ID against that user. If one doesn't exist, it will create one.

From there it will create the appropriate Stripe Checkout session and return the session ID to the frontend.

Here's an example of a form that makes use of Alpine.js to handle the donation process:

```html
<!-- donation form start -->
<div x-data="donateForm">
  <form class="bg-white shadow m-4 p-4 space-y-4 max-w-[600px]"
        @submit.prevent="makeDonation()"
        method="post">

    <div>
      <button class="px-4 py-2 border-2 border border-gray-300"
      type="button"
      :class="{'font-semibold bg-blue-500 text-white border-blue-500 hover:border-blue-500': data.frequency ===
      'recurring' }"
      @click="data.frequency = 'recurring'">
        Monthly
        </button>
       <button class="px-4 py-2 border-2 border border-gray-300"
       type="button"
       :class="{ 'font-semibold bg-blue-500 text-white border-blue-500 hover:border-blue-500': data.frequency ===
       'single' }"
       @click="data.frequency = 'single'">
       One-off
       </button>
    </div>

    <p class="font-semibold">Choose a donation amount</p>

    <div class="flex items-start mt-4">
        <template x-for="a in data.amounts">
            <button x-text="`£ ${a}`"
                type="button"
                :class="{ 'bg-blue-500 text-white border-blue-500 hover:border-blue-500': data.amount === a }"
                @click="data.amount = a"
                class="font-semibold mr-4 last:mr-0 w-[100px] h-[100px] p-4 border-2 border-gray-100 hover:border-gray-300"></button>
        </template>
    </div>
    <label class="flex items-center space-x-4">
        <span class="font-semibold">£</span>
        <input type="number"
                name="amount"
                id="amount"
                x-model="data.amount"
                required
                class="border p-2 w-full">
    </label>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label for="first_name" class="text-sm font-semibold block">
                First Name
            </label>
            <input type="text"
                name="first_name"
                id="first_name"
                required
                class="border p-2 w-full"
                x-model="data.first_name"
                placeholder="John">
        </div>
        <div>
          <label for="last_name" class="text-sm font-semibold block">
                Last Name
            </label>
          <input type="text"
                name="last_name"
                id="last_name"
                x-model="data.last_name"
                required
                class="border p-2 w-full"
                placeholder="Doe">
        </div>
    </div>
    <div>
      <label for="email" class="text-sm font-semibold block">
            Email
        </label>
      <input type="email"
            name="email"
            id="email"
            class="border p-2 w-full"
            x-model="data.email"
            required
            placeholder="johndoe@gmail.com">
    </div>
    <div>
      <button class="font-semibold bg-blue-500 hover:bg-blue-400 transition ease-in-out p-4 text-white">
        Donate
      </button>
    </div>
  </form>
</div>
<!-- /donation form -->
```

```js
document.addEventListener('alpine:init', () => {
    Alpine.data('donateForm', () => ({
        data: {
            amount: 10,
            amounts: [5, 10, 50],
            frequency: 'recurring',
            first_name: '',
            last_name: '',
            email: '',
        },

        makeDonation() {
            fetch('/donation-checkout/start', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(this.data)
            })
                .then(r => r.json())
                .then(data => window.location.href = data.url)
                .catch(error => console.log(error))
        }
    }))
})

```
Breaking it down, the form has a few things going on: it has a button group to toggle `frequency` between single and
recurring. It has a button group to select the amount to donate, in addition to a number input that allows a manual override.

It has a text input to allow the user to enter their first name, last name and email address. These are all marked
as required fields and are validated in the `POST` request to `/donation-checkout/start`. If the request is
successful, it returns a Stripe Checkout session response which is converted to JSON and the `url` is used to redirect to Stripe Checkout.

If you want to use this implementation as is, be sure to have the script tags in the head of your layout template:

```html
<script src="{{ mix src='/js/site.js' }}" defer></script>
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
```

Of course, you could handle this in plain JavaScript, Vue, React or any other framework you prefer.
