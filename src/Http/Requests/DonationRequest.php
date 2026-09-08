<?php

namespace Ghijk\DonationCheckout\Http\Requests;

use Illuminate\Support\Str;
use Illuminate\Foundation\Http\FormRequest;

class DonationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'integer', 'min:1', 'max:999999'],
            'email' => ['required', 'string', 'email:rfc', 'max:254'],
            'first_name' => ['required', 'string', 'max:100', 'not_regex:/\p{C}/u'],
            'last_name' => ['required', 'string', 'max:100', 'not_regex:/\p{C}/u'],
            'frequency' => ['required', 'string', 'in:single,recurring'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => $this->normalise($this->input('email'), lowercase: true),
            'first_name' => $this->normalise($this->input('first_name')),
            'last_name' => $this->normalise($this->input('last_name')),
        ]);
    }

    private function normalise(mixed $value, bool $lowercase = false): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $value = mb_trim($value);

        return $lowercase ? Str::lower($value) : $value;
    }
}
