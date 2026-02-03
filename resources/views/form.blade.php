<form id="donation-form" class="donation-form" method="post">
    @php $freq = $frequency ?? config('donation-checkout.default_frequency', 'recurring') @endphp
    <div class="donation-frequency">
        <button type="button"
                class="donation-frequency-btn{{ $freq === 'recurring' ? ' active' : '' }}"
                data-frequency="recurring">
            Monthly
        </button>
        <button type="button"
                class="donation-frequency-btn{{ $freq === 'single' ? ' active' : '' }}"
                data-frequency="single">
            One-off
        </button>
    </div>

    <p class="donation-label">Choose a donation amount</p>

    <div class="donation-amounts">
        @foreach($amounts as $amount)
            <button type="button"
                    class="donation-amount-btn{{ $amount == $default ? ' active' : '' }}"
                    data-amount="{{ $amount }}">
                {{ $currency_symbol }}{{ $amount }}
            </button>
        @endforeach
    </div>

    <label class="donation-custom-amount">
        <span>{{ $currency_symbol }}</span>
        <input type="number"
               name="amount"
               id="donation-amount"
               value="{{ $default }}"
               min="1"
               required>
    </label>

    <div class="donation-fields">
        <div class="donation-field">
            <label for="donation-first-name">First Name</label>
            <input type="text"
                   name="first_name"
                   id="donation-first-name"
                   required>
        </div>
        <div class="donation-field">
            <label for="donation-last-name">Last Name</label>
            <input type="text"
                   name="last_name"
                   id="donation-last-name"
                   required>
        </div>
    </div>

    <div class="donation-field">
        <label for="donation-email">Email</label>
        <input type="email"
               name="email"
               id="donation-email"
               required>
    </div>

    <div class="donation-submit">
        <button type="submit" id="donation-submit-btn" class="donation-submit-btn">
            {{ $button_text }}
        </button>
    </div>

    <p id="donation-error" class="donation-error" style="display: none;"></p>
</form>
