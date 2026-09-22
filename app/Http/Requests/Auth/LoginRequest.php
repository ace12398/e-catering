<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'login' => ['nullable', 'string'],
            'email' => ['nullable', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $loginInput = $this->input('login') ?? $this->input('email');

        if (! $loginInput) {
            throw ValidationException::withMessages([
                'login' => 'Silakan masukkan username atau email.',
            ]);
        }

        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $remember = $this->boolean('remember');

        // Try primary field (username or email)
        if (Auth::attempt([$fieldType => $loginInput, 'password' => $this->input('password')], $remember)) {
            RateLimiter::clear($this->throttleKey());
            return;
        }

        // Fallback: try the other field if first one failed
        $otherField = ($fieldType === 'email') ? 'username' : 'email';
        if (Auth::attempt([$otherField => $loginInput, 'password' => $this->input('password')], $remember)) {
            RateLimiter::clear($this->throttleKey());
            return;
        }

        RateLimiter::hit($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => trans('auth.failed'),
        ]);
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        $loginInput = $this->input('login') ?? $this->input('email') ?? '';
        return Str::transliterate(Str::lower($loginInput).'|'.$this->ip());
    }
}
