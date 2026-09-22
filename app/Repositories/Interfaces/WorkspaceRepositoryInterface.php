<?php

namespace App\Repositories\Interfaces;

use App\Models\WorkspacePreference;

interface WorkspaceRepositoryInterface extends BaseRepositoryInterface
{
    public function getByUserId(int $userId): ?WorkspacePreference;

    public function savePreference(int $userId, string $preset, array $layoutMatrix): WorkspacePreference;
}
