<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAvatarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'avatar' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.required' => 'Please select an avatar image file.',
            'avatar.mimes' => 'Avatar must be a JPEG, JPG, PNG or WEBP image format.',
            'avatar.max' => 'Avatar maximum file size is 2 MB.',
        ];
    }
}
