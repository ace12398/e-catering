<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Delivery;
use Illuminate\Support\Str;

class DeliveryObserver
{
    public function creating(Delivery $delivery): void
    {
        if (empty($delivery->uuid)) {
            $delivery->uuid = (string) Str::uuid();
        }
    }

    public function updated(Delivery $delivery): void
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'delivery_updated',
            'entity_type' => Delivery::class,
            'entity_id' => $delivery->id,
            'new_values' => ['status' => $delivery->status],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'performed_at' => now(),
        ]);
    }
}
