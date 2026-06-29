<?php

namespace App\Http\Requests;

use App\Enums\AccountEventType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AccountEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(AccountEventType::class)],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'origin' => [
                'required_if:type,' . AccountEventType::WITHDRAW->value . ',' . AccountEventType::TRANSFER->value,
                'string'
            ],
            'destination' => [
                'required_if:type,' . AccountEventType::DEPOSIT->value . ',' . AccountEventType::TRANSFER->value,
                'string'
            ],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json(0, 404)
        );
    }
}