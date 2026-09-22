<?php

namespace App\Enums;

enum WorkspacePreset: string
{
    case INSTITUTION_ORGANIZATION = 'institution_organization';
    case OPERATIONS_LOGISTICS = 'operations_logistics';
    case FINANCE_AUDIT = 'finance_audit';
    case PERSONAL_CUSTOMER = 'personal_customer';
    case MINIMAL_FOCUS = 'minimal_focus';

    public function label(): string
    {
        return match ($this) {
            self::INSTITUTION_ORGANIZATION => 'Tata Letak Instansi & Organisasi',
            self::OPERATIONS_LOGISTICS => 'Tata Letak Operasional & Dapur',
            self::FINANCE_AUDIT => 'Tata Letak Keuangan & Pembayaran',
            self::PERSONAL_CUSTOMER => 'Tata Letak Pelanggan Perorangan',
            self::MINIMAL_FOCUS => 'Tata Letak Ringkas Esensial',
        };
    }
}
