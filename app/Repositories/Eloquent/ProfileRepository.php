<?php

namespace App\Repositories\Eloquent;

use App\Models\Profile;
use App\Repositories\Interfaces\ProfileRepositoryInterface;

class ProfileRepository extends BaseRepository implements ProfileRepositoryInterface
{
    public function __construct(Profile $model)
    {
        parent::__construct($model);
    }

    public function findByUserId(int $userId): Profile
    {
        return $this->model->firstOrCreate(['user_id' => $userId]);
    }

    public function updateOrCreateProfile(int $userId, array $data): Profile
    {
        return $this->model->updateOrCreate(
            ['user_id' => $userId],
            $data
        );
    }
}
