<?php

namespace App\Repositories\Eloquent;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    public function findById(int $id): ?Order
    {
        return $this->model->with(['items.menu', 'user.profile', 'kitchenTask', 'delivery', 'invoice'])->find($id);
    }

    public function getPaginatedForUser(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model->where('user_id', $userId)
            ->with(['items.menu', 'kitchenTask', 'delivery', 'invoice'])
            ->latest()
            ->paginate($perPage);
    }

    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->with(['user.profile', 'items.menu', 'kitchenTask', 'delivery', 'invoice'])
            ->latest()
            ->paginate($perPage);
    }

    public function createOrder(array $data): Order
    {
        return $this->model->create($data);
    }

    public function updateStatus(int $id, string $status): bool
    {
        $order = $this->findById($id);
        return $order ? $order->update(['status' => $status]) : false;
    }
}
