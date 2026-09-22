<?php

namespace App\Repositories;

use App\Repositories\Contracts\AvatarRepositoryInterface;
use App\Support\Avatar\AvatarHelper;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AvatarRepository implements AvatarRepositoryInterface
{
    public function store(UploadedFile $file): string
    {
        $folder = AvatarHelper::storagePath();
        $filename = AvatarHelper::generateFilename($file->getClientOriginalExtension());
        return $file->storeAs($folder, $filename, 'public');
    }

    public function delete(?string $path): bool
    {
        return AvatarHelper::deleteOldAvatar($path);
    }

    public function exists(?string $path): bool
    {
        return $path ? Storage::disk('public')->exists($path) : false;
    }

    public function getPublicUrl(?string $path, string $fallbackName = 'User'): string
    {
        return AvatarHelper::publicUrl($path, $fallbackName);
    }
}
