<?php

namespace App\Services;

use App\Models\Delivery;
use App\Repositories\Contracts\DeliveryRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DeliveryService
{
    public function __construct(
        protected DeliveryRepositoryInterface $deliveryRepo
    ) {}

    public function pickup(Delivery $delivery): bool
    {
        return DB::transaction(function () use ($delivery) {
            $delivery->update([
                'status' => 'dalam_pengiriman',
                'pickup_time' => now(),
            ]);
            $delivery->order?->update(['status' => 'sedang_dikirim']);
            return true;
        });
    }

    public function complete(Delivery $delivery): bool
    {
        return DB::transaction(function () use ($delivery) {
            $delivery->update([
                'status' => 'selesai',
                'delivered_time' => now(),
            ]);
            $delivery->order?->update(['status' => 'selesai']);
            return true;
        });
    }

    public function updateStatus(Delivery $delivery, string $status): bool
    {
        return match ($status) {
            'pickup'   => $this->pickup($delivery),
            'complete' => $this->complete($delivery),
            default    => $this->deliveryRepo->updateStatus($delivery->id, $status),
        };
    }
}
