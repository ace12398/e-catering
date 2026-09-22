<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string'],
            'delivery_address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
