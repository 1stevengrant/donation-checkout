<style>
    .donation-form {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        max-width: 28rem;
        margin: 0 auto;
    }

    .donation-frequency {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
    }

    .donation-frequency-btn {
        flex: 1;
        padding: 0.625rem 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 0.5rem;
        background: #fff;
        color: #374151;
        font-size: 0.9375rem;
        font-weight: 500;
        cursor: pointer;
        transition: border-color 0.15s, background-color 0.15s, color 0.15s;
    }

    .donation-frequency-btn:hover {
        border-color: #d1d5db;
        background: #f9fafb;
    }

    .donation-frequency-btn.active {
        border-color: #4f46e5;
        background: #4f46e5;
        color: #fff;
    }

    .donation-label {
        margin: 0 0 0.75rem;
        font-size: 0.9375rem;
        font-weight: 500;
        color: #374151;
    }

    .donation-amounts {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .donation-amount-btn {
        flex: 1;
        padding: 0.625rem 0.5rem;
        border: 2px solid #e5e7eb;
        border-radius: 0.5rem;
        background: #fff;
        color: #374151;
        font-size: 0.9375rem;
        font-weight: 600;
        cursor: pointer;
        transition: border-color 0.15s, background-color 0.15s, color 0.15s;
    }

    .donation-amount-btn:hover {
        border-color: #d1d5db;
        background: #f9fafb;
    }

    .donation-amount-btn.active {
        border-color: #4f46e5;
        background: #eef2ff;
        color: #4f46e5;
    }

    .donation-custom-amount {
        display: flex;
        align-items: center;
        border: 2px solid #e5e7eb;
        border-radius: 0.5rem;
        overflow: hidden;
        margin-bottom: 1.5rem;
        transition: border-color 0.15s;
    }

    .donation-custom-amount:focus-within {
        border-color: #4f46e5;
    }

    .donation-custom-amount span {
        padding: 0.625rem 0.75rem;
        background: #f9fafb;
        color: #6b7280;
        font-size: 0.9375rem;
        font-weight: 500;
        border-right: 2px solid #e5e7eb;
    }

    .donation-custom-amount input {
        flex: 1;
        padding: 0.625rem 0.75rem;
        border: none;
        outline: none;
        font-size: 0.9375rem;
        color: #111827;
        background: #fff;
    }

    .donation-fields {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .donation-field {
        margin-bottom: 1rem;
    }

    .donation-fields .donation-field {
        margin-bottom: 0;
    }

    .donation-field label {
        display: block;
        margin-bottom: 0.375rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
    }

    .donation-field input {
        width: 100%;
        padding: 0.625rem 0.75rem;
        border: 2px solid #e5e7eb;
        border-radius: 0.5rem;
        font-size: 0.9375rem;
        color: #111827;
        background: #fff;
        transition: border-color 0.15s;
        box-sizing: border-box;
    }

    .donation-field input:focus {
        outline: none;
        border-color: #4f46e5;
    }

    .donation-submit {
        margin-top: 1.5rem;
    }

    .donation-submit-btn {
        width: 100%;
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 0.5rem;
        background: #4f46e5;
        color: #fff;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.15s;
    }

    .donation-submit-btn:hover {
        background: #4338ca;
    }

    .donation-submit-btn:disabled {
        background: #9ca3af;
        cursor: not-allowed;
    }

    .donation-error {
        margin-top: 1rem;
        padding: 0.75rem;
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 0.5rem;
        color: #dc2626;
        font-size: 0.875rem;
    }
</style>
