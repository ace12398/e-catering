<?php

namespace App\Listeners\Profile;

use App\Events\ProfileUpdated;
use App\Models\ActivityLog;

class WriteProfileActivityLog
{
    public function handle(ProfileUpdated $event): void
    {
        ActivityLog::create([
            'user_id' => $event->profile->user_id,
            'action' => 'profile_updated',
            'entity_type' => get_class($event->profile),
            'entity_id' => $event->profile->id,
            'new_values' => [
                'completion' => $event->profile->completion_percentage,
                'company' => $event->profile->company_name,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'performed_at' => now(),
        ]);
    }
}
