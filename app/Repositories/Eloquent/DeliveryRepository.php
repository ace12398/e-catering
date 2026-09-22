<?php

namespace App\Repositories\Eloquent;

use App\Models\Delivery;
use App\Repositories\Contracts\DeliveryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DeliveryRepository extends BaseRepository implements DeliveryRepositoryInterface
{
    public function __construct(Delivery $model)
    {
        parent::__construct($model);
    }

    public function getPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return $this->model->with(['order', 'courier'])->latest()->paginate($perPage);
    }

    public function findById(int $id): ?Delivery
    {
        return $this->model->with(['order', 'courier'])->find($id);
    }

    public function assignCourier(int $deliveryId, int $courierId): bool
    {
        $delivery = $this->findById($deliveryId);
        return $delivery ? $delivery->update(['courier_id' => $courierId, 'status' => 'assigned']) : false;
    }

    public function updateStatus(int $id, string $status): bool
    {
        $delivery = $this->findById($id);
        if (!$delivery) return false;
        
        return \Illuminate\Support\Facades\DB::transaction(function() use ($delivery, $status) {
            $data = ['status' => $status];
            if ($status === 'dalam_pengiriman') {
                $data['pickup_time'] = now();
                $delivery->order?->update(['status' => 'sedang_dikirim']);
            } elseif ($status === 'selesai') {
                $data['delivered_time'] = now();
                $delivery->order?->update(['status' => 'selesai']);
            }
            return $delivery->update($data);
        });
    }
}
