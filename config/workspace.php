<?php

return [
    'default_preset' => env('WORKSPACE_DEFAULT_PRESET', 'institution_organization'),
    'cache_ttl' => env('WORKSPACE_CACHE_TTL', 86400),
    'grid_columns' => 12,
    'presets' => [
        'institution_organization' => 'Tata Letak Instansi & Organisasi',
        'operations_logistics' => 'Tata Letak Operasional & Dapur',
        'finance_audit' => 'Tata Letak Keuangan & Pembayaran',
        'personal_customer' => 'Tata Letak Pelanggan Perorangan',
        'minimal_focus' => 'Tata Letak Ringkas Esensial',
    ]
];
