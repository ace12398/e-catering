<?php

namespace App\Observers;

use App\Events\Profile\ProfileCompleted;
use App\Models\ActivityLog;
use App\Models\Profile;
use Illuminate\Support\Facades\Log;

class ProfileObserver
{
    public function saving(Profile $profile): void
    {
        try {
            if ($profile->company_name) {
                $profile->company_name = trim($profile->company_name);
            }
            if ($profile->phone_number) {
                $profile->phone_number = preg_replace('/[^\d+]/', '', $profile->phone_number);
            }

            $profile->completion_percentage = $profile->calculateCompletion();
        } catch (\Throwable $e) {
            Log::error('ProfileObserver saving error', ['error' => $e->getMessage()]);
        }
    }

    public function updated(Profile $profile): void
    {
        try {
            if ($profile->completion_percentage === 100 && $profile->wasChanged('completion_percentage')) {
                event(new ProfileCompleted($profile));
            }

            ActivityLog::create([
                'user_id' => $profile->user_id,
                'action' => 'profile_updated',
                'entity_type' => Profile::class,
                'entity_id' => $profile->id,
                'old_values' => $profile->getOriginal(),
                'new_values' => $profile->getChanges(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'performed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('ProfileObserver updated error', ['error' => $e->getMessage()]);
        }
    }
}
