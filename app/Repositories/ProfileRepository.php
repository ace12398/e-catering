<?php

namespace App\Repositories;

use App\Models\Profile;
use App\Models\User;
use App\Repositories\Contracts\ProfileRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileRepository implements ProfileRepositoryInterface
{
    public function findByUser(int $userId): Profile
    {
        return Profile::firstOrCreate(['user_id' => $userId]);
    }

    public function updateProfile(User $user, array $data): Profile
    {
        $profile = $this->findByUser($user->id);
        $profile->update($data);
        return $profile->fresh();
    }

    public function uploadAvatar(User $user, UploadedFile $file): Profile
    {
        $profile = $this->findByUser($user->id);

        if ($profile->avatar && Storage::disk('public')->exists($profile->avatar)) {
            Storage::disk('public')->delete($profile->avatar);
        }

        $folder = 'avatars/' . date('Y/m');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($folder, $filename, 'public');

        $profile->update(['avatar' => $path]);

        return $profile->fresh();
    }

    public function deleteAvatar(User $user): bool
    {
        $profile = $this->findByUser($user->id);

        if ($profile->avatar && Storage::disk('public')->exists($profile->avatar)) {
            Storage::disk('public')->delete($profile->avatar);
        }

        return $profile->update(['avatar' => null]);
    }

    public function calculateCompletion(Profile $profile): int
    {
        return $profile->calculateCompletion();
    }

    public function getCompletionPercentage(Profile $profile): int
    {
        return $profile->completion_percentage ?? $this->calculateCompletion($profile);
    }
}
