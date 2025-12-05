<script>
(function() {
    const form = document.getElementById('donation-form');
    if (!form) return;

    const amountInput = document.getElementById('donation-amount');
    const submitBtn = document.getElementById('donation-submit-btn');
    const errorMessage = document.getElementById('donation-error');
    const submitBtnText = submitBtn.textContent;
    let frequency = '{{ $frequency ?? config('donation-checkout.default_frequency', 'recurring') }}';

    // Frequency toggle
    document.querySelectorAll('.donation-frequency-btn').forEach(btn => {
        if (btn.dataset.frequency === frequency) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }

        btn.addEventListener('click', function() {
            document.querySelectorAll('.donation-frequency-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            frequency = this.dataset.frequency;
        });
    });

    // Amount buttons
    document.querySelectorAll('.donation-amount-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.donation-amount-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            amountInput.value = this.dataset.amount;
        });
    });

    // Update active state when typing custom amount
    amountInput.addEventListener('input', function() {
        document.querySelectorAll('.donation-amount-btn').forEach(b => b.classList.remove('active'));
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
            first_name: document.getElementById('donation-first-name').value,
            last_name: document.getElementById('donation-last-name').value,
            email: document.getElementById('donation-email').value
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
            submitBtn.textContent = submitBtnText;
            errorMessage.textContent = error.message || 'An error occurred. Please try again.';
            errorMessage.style.display = 'block';
        });
    });
})();
</script>
