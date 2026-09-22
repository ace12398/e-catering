<?php

namespace App\Services\Avatar;

use App\Exceptions\InvalidAvatarException;

use App\Repositories\Contracts\AvatarRepositoryInterface;
use App\Support\Avatar\AvatarHelper;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class AvatarService
{
    public function __construct(
        protected AvatarRepositoryInterface $avatarRepo
    ) {}

    public function processUpload(UploadedFile $file, ?string $oldPath = null): string
    {
        $this->verifyMime($file);

        if ($oldPath) {
            $this->removeAvatar($oldPath);
        }

        try {
            return $this->avatarRepo->store($file);
        } catch (\Throwable $e) {
            Log::error('Avatar Storage Failure', ['error' => $e->getMessage()]);
            throw new InvalidAvatarException('Failed to process and store uploaded avatar file.');
        }
    }

    public function removeAvatar(?string $path): bool
    {
        if ($path) {
            AvatarHelper::deleteThumbnail($path);
            return $this->avatarRepo->delete($path);
        }
        return false;
    }

    protected function verifyMime(UploadedFile $file): void
    {
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
        if (! in_array($file->getMimeType(), $allowedMimes)) {
            Log::warning('Rejected Invalid Avatar File MIME', ['mime' => $file->getMimeType()]);
            throw new InvalidAvatarException('Invalid file format. Only JPEG, PNG, and WEBP image files are permitted.');
        }
    }
}
