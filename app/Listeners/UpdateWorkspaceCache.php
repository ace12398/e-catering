<?php

namespace App\Listeners;

use App\Events\WorkspaceSaved;
use Illuminate\Support\Facades\Cache;

class UpdateWorkspaceCache
{
    public function handle(WorkspaceSaved $event): void
    {
        $cacheKey = "user_workspace_{$event->userId}";
        Cache::put($cacheKey, $event->layoutMatrix, config('workspace.cache_ttl', 86400));
    }
}
