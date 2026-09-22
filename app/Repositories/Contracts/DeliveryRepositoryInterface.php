<?php

namespace App\Repositories\Contracts;

use App\Models\Delivery;
use App\Repositories\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface DeliveryRepositoryInterface extends BaseRepositoryInterface
{
    public function getPaginated(int $perPage = 10): LengthAwarePaginator;
    public function findById(int $id): ?Delivery;
    public function assignCourier(int $deliveryId, int $courierId): bool;
    public function updateStatus(int $id, string $status): bool;
}
