<?php

namespace App\Listeners\Workspace;

use App\Events\Workspace\WorkspaceChanged;
use Illuminate\Support\Facades\Cache;

class RefreshDashboardWidgets
{
    public function handle(WorkspaceChanged $event): void
    {
        $cacheKey = "user_workspace_{$event->userId}";
        Cache::put($cacheKey, $event->layoutMatrix, 86400);
    }
}
