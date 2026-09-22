<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case UNPAID = 'belum_dibayar';
    case WAITING_VERIFICATION = 'menunggu_verifikasi';
    case PAID = 'lunas';
    case REJECTED = 'ditolak';

    public function label(): string
    {
        return match($this) {
            self::UNPAID => 'Belum Dibayar',
            self::WAITING_VERIFICATION => 'Menunggu Verifikasi',
            self::PAID => 'Lunas',
            self::REJECTED => 'Ditolak',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::UNPAID => 'amber',
            self::WAITING_VERIFICATION => 'orange',
            self::PAID => 'emerald',
            self::REJECTED => 'rose',
        };
    }
}
