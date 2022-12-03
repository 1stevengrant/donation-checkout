<?php

namespace Ghijk\DonationCheckout\Http\Requests;

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
            'amount' => 'required|numeric|min:1|max:999999',
            'email' => 'required|email',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'frequency' => 'required|string',
        ];
    }
}
