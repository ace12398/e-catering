<?php

namespace App\Services;

use App\Models\KitchenTask;
use App\Repositories\Contracts\KitchenRepositoryInterface;
use Illuminate\Support\Facades\DB;

class KitchenService
{
    public function __construct(
        protected KitchenRepositoryInterface $kitchenRepo
    ) {}

    public function updateStatus(KitchenTask $task, string $newStatus): bool
    {
        return DB::transaction(function () use ($task, $newStatus) {
            $data = ['status' => $newStatus];

            if ($newStatus === 'cooking') {
                $data['started_at'] = now();
                // Update order status
                $task->order?->update(['status' => 'sedang_dimasak']);
            } elseif ($newStatus === 'done') {
                $data['finished_at'] = now();
                // Order siap dikirim
                $task->order?->update(['status' => 'sedang_dikirim']);
                // Delivery status aktif
                $task->order?->delivery?->update(['status' => 'siap_diambil']);
            } elseif ($newStatus === 'packing') {
                $task->order?->update(['status' => 'sedang_dimasak']);
            }

            return $task->update($data);
        });
    }
}
