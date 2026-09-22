<?php

namespace App\Observers;

use App\Events\Workspace\WorkspaceChanged;
use App\Models\WorkspacePreference;
use Illuminate\Support\Facades\Log;

class WorkspaceObserver
{
    public function updated(WorkspacePreference $workspace): void
    {
        try {
            event(new WorkspaceChanged(
                $workspace->user_id,
                $workspace->active_preset ?? 'standar',
                $workspace->layout_matrix ?? []
            ));
            app(\App\Services\AdaptiveEngine::class)->clearCache($workspace->user_id);
        } catch (\Throwable $e) {
            Log::error('WorkspaceObserver Error', ['error' => $e->getMessage()]);
        }
    }
}
