<?php

namespace App\Repositories\Contracts;

use App\Models\Order;
use App\Repositories\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface extends BaseRepositoryInterface
{
    public function findById(int $id): ?Order;

    public function getPaginatedForUser(int $userId, int $perPage = 10): LengthAwarePaginator;

    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator;

    public function createOrder(array $data): Order;

    public function updateStatus(int $id, string $status): bool;
}
