<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'company_name' => ['nullable', 'string', 'max:150'],
            'phone_number' => ['nullable', 'string', 'regex:/^[0-9+\-\s()]+$/', 'min:8', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'phone_number.regex' => 'Format nomor telepon/WhatsApp tidak valid.',
            'avatar.image' => 'File foto profil harus berupa gambar.',
            'avatar.mimes' => 'Format gambar yang diperbolehkan: JPEG, JPG, PNG, WEBP.',
            'avatar.max' => 'Ukuran maksimal file foto adalah 2 MB.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nama Lengkap',
            'phone_number' => 'Nomor Telepon',
            'company_name' => 'Nama Perusahaan',
            'address' => 'Alamat Pengiriman',
            'avatar' => 'Foto Profil',
        ];
    }
}
