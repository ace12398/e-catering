<?php

namespace App\Enums;

enum OrderStatus: string
{
    case WAITING_PAYMENT = 'menunggu_pembayaran';
    case WAITING_VERIFICATION = 'menunggu_verifikasi';
    case PROCESSING = 'sedang_diproses';
    case COOKING = 'sedang_dimasak';
    case ON_DELIVERY = 'sedang_dikirim';
    case COMPLETED = 'selesai';
    case CANCELLED = 'dibatalkan';

    public function label(): string
    {
        return match($this) {
            self::WAITING_PAYMENT => 'Menunggu Pembayaran',
            self::WAITING_VERIFICATION => 'Menunggu Verifikasi',
            self::PROCESSING => 'Sedang Diproses',
            self::COOKING => 'Sedang Dimasak',
            self::ON_DELIVERY => 'Sedang Dikirim',
            self::COMPLETED => 'Selesai',
            self::CANCELLED => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::WAITING_PAYMENT => 'amber',
            self::WAITING_VERIFICATION => 'orange',
            self::PROCESSING => 'blue',
            self::COOKING => 'indigo',
            self::ON_DELIVERY => 'indigo',
            self::COMPLETED => 'emerald',
            self::CANCELLED => 'rose',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::WAITING_PAYMENT => '🕐',
            self::WAITING_VERIFICATION => '📋',
            self::PROCESSING => '✔',
            self::COOKING => '🍳',
            self::ON_DELIVERY => '🚚',
            self::COMPLETED => '✅',
            self::CANCELLED => '❌',
        };
    }

    public function canUploadProof(): bool
    {
        return $this === self::WAITING_PAYMENT;
    }

    public function canCancel(): bool
    {
        return in_array($this, [self::WAITING_PAYMENT, self::WAITING_VERIFICATION]);
    }
}
