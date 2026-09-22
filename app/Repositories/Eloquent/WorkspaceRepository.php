<?php

namespace App\Repositories\Eloquent;

use App\Models\WorkspacePreference;
use App\Repositories\Interfaces\WorkspaceRepositoryInterface;

class WorkspaceRepository extends BaseRepository implements WorkspaceRepositoryInterface
{
    public function __construct(WorkspacePreference $model)
    {
        parent::__construct($model);
    }

    public function getByUserId(int $userId): ?WorkspacePreference
    {
        return $this->model->where('user_id', $userId)->first();
    }

    public function savePreference(int $userId, string $preset, array $layoutMatrix): WorkspacePreference
    {
        return $this->model->updateOrCreate(
            ['user_id' => $userId],
            [
                'preset' => $preset,
                'layout_matrix' => $layoutMatrix,
                'auto_save' => true,
            ]
        );
    }
}
