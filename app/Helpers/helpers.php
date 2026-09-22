<?php

if (!function_exists('format_idr')) {
    /**
     * Format numerical amount to Indonesian Rupiah (IDR).
     */
    function format_idr(float|int $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}

if (!function_exists('format_date')) {
    /**
     * Format date string to Indonesian localized date format.
     */
    function format_date(mixed $date, string $format = 'd M Y, H:i'): string
    {
        if (!$date) return '-';
        return \Carbon\Carbon::parse($date)->translatedFormat($format);
    }
}

if (!function_exists('response_success')) {
    /**
     * Standardized API success JSON response.
     */
    function response_success(mixed $data = null, string $message = 'Success', int $code = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'timestamp' => now()->toIso8601String()
        ], $code);
    }
}

if (!function_exists('response_error')) {
    /**
     * Standardized API error JSON response.
     */
    function response_error(string $message = 'Error', int $code = 400, mixed $errors = null): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
            'timestamp' => now()->toIso8601String()
        ], $code);
    }
}
