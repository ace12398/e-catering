<?php

namespace App\Events;

use App\Models\Profile;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AvatarUploaded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Profile $profile,
        public readonly string $avatarPath
    ) {}
}
