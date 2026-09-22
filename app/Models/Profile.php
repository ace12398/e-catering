<?php

namespace App\Models;

use App\Support\Avatar\AvatarHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'phone_number',
        'address',
        'avatar',
        'completion_percentage',
    ];

    protected $casts = [
        'completion_percentage' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getAvatarUrlAttribute(): string
    {
        return AvatarHelper::publicUrl($this->avatar, $this->user->name ?? 'User');
    }

    public function getThumbnailUrlAttribute(): string
    {
        return AvatarHelper::thumbnailUrl($this->avatar, $this->user->name ?? 'User');
    }

    public function getDefaultAvatarAttribute(): string
    {
        return AvatarHelper::defaultAvatar($this->user->name ?? 'User');
    }

    public function hasAvatar(): bool
    {
        return !empty($this->avatar);
    }

    public function isProfileComplete(): bool
    {
        return $this->calculateCompletion() >= 100;
    }

    /**
     * Calculate profile completion percentage based on 5 core items (20% each):
     * 1. Nama (20%)
     * 2. Username (20%)
     * 3. Nomor HP (20%)
     * 4. Alamat (20%)
     * 5. Foto Profil (20%)
     */
    public function calculateCompletion(): int
    {
        $percentage = 0;

        $user = $this->user;
        if ($user && !empty($user->name)) $percentage += 20;
        if ($user && !empty($user->username)) $percentage += 20;
        if (!empty($this->phone_number)) $percentage += 20;
        if (!empty($this->address)) $percentage += 20;
        if (!empty($this->avatar)) $percentage += 20;

        return min(100, $percentage);
    }
}
