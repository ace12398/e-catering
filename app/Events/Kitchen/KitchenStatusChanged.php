<?php

namespace App\Events\Kitchen;

use App\Models\KitchenTask;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KitchenStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly KitchenTask $task,
        public readonly string $previousStatus,
        public readonly string $newStatus
    ) {}
}
