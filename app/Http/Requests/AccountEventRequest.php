<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccountEventRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(['deposit', 'withdraw', 'transfer'])],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'destination' => ['required_if:type,deposit,transfer', 'string'],
            'origin' => ['required_if:type,withdraw,transfer', 'string'],
        ];
    }
}