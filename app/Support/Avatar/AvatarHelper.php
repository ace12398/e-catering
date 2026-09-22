<?php

namespace App\Support\Avatar;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AvatarHelper
{
    public static function generateFilename(string $extension = 'webp'): string
    {
        return Str::uuid() . '.' . $extension;
    }

    public static function storagePath(): string
    {
        return 'avatars/' . date('Y/m');
    }

    public static function getFolderPath(): string
    {
        return static::storagePath();
    }

    public static function publicUrl(?string $path, string $fallbackName = 'User'): string
    {
        if (!$path) {
            return static::defaultAvatar($fallbackName);
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::url($path);
        }

        return static::defaultAvatar($fallbackName);
    }

    public static function getPublicUrl(?string $path, string $fallbackName = 'User'): string
    {
        return static::publicUrl($path, $fallbackName);
    }

    public static function thumbnailUrl(?string $path, string $fallbackName = 'User'): string
    {
        if ($path) {
            $thumbPath = str_replace('avatars/', 'avatars/thumbnails/', $path);
            if (Storage::disk('public')->exists($thumbPath)) {
                return Storage::url($thumbPath);
            }
        }
        return static::publicUrl($path, $fallbackName);
    }

    public static function defaultAvatar(string $fallbackName = 'User'): string
    {
        return 'https://ui-avatars.com/api/?name=' . urlencode($fallbackName) . '&color=0D9488&background=F0FDFA';
    }

    public static function deleteOldAvatar(?string $path): bool
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            static::deleteThumbnail($path);
            return true;
        }
        return false;
    }

    public static function deleteThumbnail(?string $path): bool
    {
        if ($path) {
            $thumbPath = str_replace('avatars/', 'avatars/thumbnails/', $path);
            if (Storage::disk('public')->exists($thumbPath)) {
                return Storage::disk('public')->delete($thumbPath);
            }
        }
        return false;
    }
}
