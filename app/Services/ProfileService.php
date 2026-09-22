<?php

namespace App\Services;

use App\Events\AvatarDeleted;
use App\Events\AvatarUploaded;
use App\Events\ProfileUpdated;
use App\Models\Profile;
use App\Models\User;
use App\Repositories\Contracts\ProfileRepositoryInterface;
use App\Services\Avatar\AvatarService;
use Illuminate\Http\UploadedFile;

class ProfileService
{
    public function __construct(
        protected ProfileRepositoryInterface $profileRepo,
        protected AvatarService $avatarService
    ) {}

    public function updateProfile(User $user, array $data, ?UploadedFile $avatarFile = null): User
    {
        $user->update(['name' => $data['name']]);

        $profileData = array_filter([
            'company_name' => $data['company_name'] ?? null,
            'phone_number' => $data['phone_number'] ?? null,
            'address' => $data['address'] ?? null,
        ], fn ($val) => !is_null($val));

        $profile = $this->profileRepo->updateProfile($user, $profileData);

        if ($avatarFile) {
            $profile = $this->uploadAvatar($user, $avatarFile);
        }

        $this->dispatchProfileUpdated($profile);

        return $user->fresh('profile');
    }

    public function uploadAvatar(User $user, UploadedFile $file): Profile
    {
        $profile = $this->profileRepo->findByUser($user->id);
        $newPath = $this->avatarService->processUpload($file, $profile->avatar);

        $profile = $this->profileRepo->updateProfile($user, ['avatar' => $newPath]);

        $this->dispatchAvatarUploaded($profile, $newPath);

        return $profile;
    }

    public function deleteAvatar(User $user): bool
    {
        $profile = $this->profileRepo->findByUser($user->id);
        if ($profile->avatar) {
            $this->avatarService->removeAvatar($profile->avatar);
            $this->profileRepo->updateProfile($user, ['avatar' => null]);
            $this->dispatchAvatarDeleted($profile);
            return true;
        }
        return false;
    }

    public function calculateCompletion(Profile $profile): int
    {
        return $this->profileRepo->calculateCompletion($profile);
    }

    public function dispatchProfileUpdated(Profile $profile): void
    {
        event(new ProfileUpdated($profile));
    }

    public function dispatchAvatarUploaded(Profile $profile, string $path): void
    {
        event(new AvatarUploaded($profile, $path));
    }

    public function dispatchAvatarDeleted(Profile $profile): void
    {
        event(new AvatarDeleted($profile));
    }
}
