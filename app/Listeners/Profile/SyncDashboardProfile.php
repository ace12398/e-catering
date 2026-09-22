<?php

namespace App\Listeners\Profile;

use App\Events\ProfileUpdated;
use Illuminate\Support\Facades\Cache;

class SyncDashboardProfile
{
    public function handle(ProfileUpdated $event): void
    {
        $cacheKey = "user_profile_{$event->profile->user_id}";
        Cache::put($cacheKey, $event->profile, 86400);
    }
}
