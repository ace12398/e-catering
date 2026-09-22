<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Invoice;
use Illuminate\Support\Str;

class InvoiceObserver
{
    public function creating(Invoice $invoice): void
    {
        if (empty($invoice->uuid)) {
            $invoice->uuid = (string) Str::uuid();
        }
        if (empty($invoice->invoice_number)) {
            $invoice->invoice_number = 'INV-' . strtoupper(Str::random(8));
        }
    }

    public function created(Invoice $invoice): void
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'invoice_generated',
            'entity_type' => Invoice::class,
            'entity_id' => $invoice->id,
            'new_values' => ['invoice_number' => $invoice->invoice_number, 'amount' => $invoice->grand_total],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'performed_at' => now(),
        ]);
    }
}
