<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            ['name' => 'Bank Mandiri Virtual Account', 'code' => 'MANDIRI_VA', 'type' => 'virtual_account', 'account_number' => '8801234567891049'],
            ['name' => 'BCA Virtual Account', 'code' => 'BCA_VA', 'type' => 'virtual_account', 'account_number' => '8801987654321049'],
            ['name' => 'QRIS Instant Scan', 'code' => 'QRIS', 'type' => 'qris', 'account_number' => 'ID1029384756'],
            ['name' => 'Invoice Pembayaran Terjadwal', 'code' => 'SCHEDULED_INVOICE', 'type' => 'invoice', 'account_number' => 'INV-DIRECT-01'],
        ];

        foreach ($methods as $pm) {
            PaymentMethod::updateOrCreate(
                ['code' => $pm['code']],
                $pm
            );
        }
    }
}
