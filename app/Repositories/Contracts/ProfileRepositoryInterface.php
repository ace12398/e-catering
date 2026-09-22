<?php

namespace App\Repositories\Contracts;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\UploadedFile;

interface ProfileRepositoryInterface
{
    public function findByUser(int $userId): Profile;

    public function updateProfile(User $user, array $data): Profile;

    public function uploadAvatar(User $user, UploadedFile $file): Profile;

    public function deleteAvatar(User $user): bool;

    public function calculateCompletion(Profile $profile): int;

    public function getCompletionPercentage(Profile $profile): int;
}
