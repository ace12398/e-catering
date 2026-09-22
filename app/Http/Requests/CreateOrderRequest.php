<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'delivery_address'  => ['required', 'string', 'max:500'],
            'payment_method_id' => ['nullable', 'exists:payment_methods,id'],
            'delivery_date'     => ['nullable', 'date'],
            'notes'             => ['nullable', 'string', 'max:500'],
        ];
    }
}
