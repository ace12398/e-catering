<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\KitchenTask;
use Illuminate\Support\Str;

class KitchenTaskObserver
{
    public function creating(KitchenTask $task): void
    {
        if (empty($task->uuid)) {
            $task->uuid = (string) Str::uuid();
        }
    }

    public function updated(KitchenTask $task): void
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'kitchen_status_updated',
            'entity_type' => KitchenTask::class,
            'entity_id' => $task->id,
            'new_values' => ['status' => $task->status],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'performed_at' => now(),
        ]);
    }
}
