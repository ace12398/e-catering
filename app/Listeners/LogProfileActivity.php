<?php

namespace App\Listeners;

use App\Events\ProfileUpdated;
use App\Models\ActivityLog;

class LogProfileActivity
{
    public function handle(ProfileUpdated $event): void
    {
        ActivityLog::create([
            'user_id' => $event->profile->user_id,
            'action' => 'profile_updated',
            'entity_type' => 'App\Models\Profile',
            'entity_id' => $event->profile->id,
            'old_values' => null,
            'new_values' => [
                'completion_percentage' => $event->profile->completion_percentage,
                'company_name' => $event->profile->company_name,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'performed_at' => now(),
        ]);
    }
}
