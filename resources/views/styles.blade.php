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

    .donation-custom-fields {
        margin-top: 1.5rem;
    }

    .donation-custom-fields .donation-field {
        margin-bottom: 0;
    }

    .donation-toggle {
        display: flex;
        align-items: center;
        gap: 1.25rem;
        cursor: pointer;
        -webkit-user-select: none;
        user-select: none;
        padding: 0.625rem 0.75rem;
        border: 2px solid #e5e7eb;
        border-radius: 0.5rem;
        transition: border-color 0.15s, background-color 0.15s;
    }

    .donation-toggle:hover {
        border-color: #d1d5db;
    }

    .donation-toggle:has(.donation-toggle-input:checked) {
        border-color: #4f46e5;
        background: #eef2ff;
    }

    .donation-toggle-input {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
        pointer-events: none;
    }

    .donation-toggle-track {
        position: relative;
        display: inline-flex;
        align-items: center;
        flex: 0 0 2.75rem;
        width: 2.75rem;
        min-width: 2.75rem;
        height: 1.5rem;
        background: #d1d5db;
        border-radius: 9999px;
        transition: background-color 0.2s;
        vertical-align: middle;
    }

    .donation-toggle-thumb {
        position: absolute;
        top: 0.125rem;
        left: 0.125rem;
        width: 1.25rem;
        height: 1.25rem;
        background: #fff;
        border-radius: 9999px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
        transition: transform 0.2s;
    }

    .donation-toggle:has(.donation-toggle-input:checked) .donation-toggle-track {
        background: #4f46e5;
    }

    .donation-toggle:has(.donation-toggle-input:checked) .donation-toggle-thumb {
        transform: translateX(1.25rem);
    }

    .donation-toggle:has(.donation-toggle-input:focus-visible) {
        outline: 2px solid #4f46e5;
        outline-offset: 2px;
    }

    .donation-toggle-label {
        flex: 1;
        font-size: 0.9375rem;
        font-weight: 500;
        color: #374151;
        line-height: 1.4;
        margin-left: 0.5rem;
    }

    .donation-magic-link {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        max-width: 28rem;
        margin: 1.5rem auto 0;
        padding: 1.5rem;
        border: 2px solid #e5e7eb;
        border-radius: 0.75rem;
        text-align: center;
    }

    .donation-magic-link-heading {
        margin: 0 0 1rem;
        font-size: 0.9375rem;
        font-weight: 500;
        color: #6b7280;
    }

    .donation-magic-link-form .donation-field {
        text-align: left;
    }

    .donation-magic-link-form .donation-submit {
        margin-top: 1rem;
    }

    .donation-magic-link-form .donation-submit-btn {
        background: #374151;
    }

    .donation-magic-link-form .donation-submit-btn:hover {
        background: #1f2937;
    }

    .donation-portal-link {
        display: inline-block;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 0.9375rem;
        font-weight: 500;
        color: #4f46e5;
        text-decoration: none;
        transition: color 0.15s;
    }

    .donation-portal-link:hover {
        color: #4338ca;
    }
</style>
