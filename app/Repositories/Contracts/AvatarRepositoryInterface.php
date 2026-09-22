<?php

namespace App\Repositories\Contracts;

use Illuminate\Http\UploadedFile;

interface AvatarRepositoryInterface
{
    public function store(UploadedFile $file): string;

    public function delete(?string $path): bool;

    public function exists(?string $path): bool;

    public function getPublicUrl(?string $path, string $fallbackName = 'User'): string;
}
