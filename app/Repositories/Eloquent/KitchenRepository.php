<?php

namespace App\Repositories\Eloquent;

use App\Models\KitchenTask;
use App\Repositories\Contracts\KitchenRepositoryInterface;
use Illuminate\Support\Collection;

class KitchenRepository extends BaseRepository implements KitchenRepositoryInterface
{
    public function __construct(KitchenTask $model)
    {
        parent::__construct($model);
    }

    public function getKanbanTasks(): Collection
    {
        return $this->model->with(['order', 'chef'])->latest()->get();
    }

    public function updateTaskStatus(int $taskId, string $status): bool
    {
        $task = $this->model->find($taskId);
        return $task ? $task->update(['status' => $status]) : false;
    }

    public function findById(int $id): ?KitchenTask
    {
        return $this->model->with(['order.user', 'chef'])->find($id);
    }

    public function getPaginated(int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->model->with(['order.user', 'chef'])->latest()->paginate($perPage);
    }
}
