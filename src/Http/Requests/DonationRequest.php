<?php

namespace Ghijk\DonationCheckout\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Ghijk\DonationCheckout\Support\Settings;

class DonationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'amount' => ['required', 'integer', 'min:1', 'max:999999'],
            'email' => ['required', 'email'],
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'frequency' => ['required', 'string', 'in:single,recurring'],
        ];

        $customFields = Settings::customFields();

        foreach ($customFields as $field => $config) {
            $fieldRules = ['nullable'];

            $type = $config['type'] ?? 'text';

            if ($type === 'checkbox') {
                $fieldRules[] = 'boolean';
            } else {
                $fieldRules[] = 'string';
                $fieldRules[] = 'max:500';
            }

            $rules[$field] = $fieldRules;
        }

        return $rules;
    }
}
