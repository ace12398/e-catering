<?php

namespace App\Repositories\Interfaces;

use App\Models\Profile;

interface ProfileRepositoryInterface extends BaseRepositoryInterface
{
    public function findByUserId(int $userId): Profile;

    public function updateOrCreateProfile(int $userId, array $data): Profile;
}
