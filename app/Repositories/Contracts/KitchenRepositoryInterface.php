<?php

namespace App\Repositories\Contracts;

use App\Models\KitchenTask;
use App\Repositories\Interfaces\BaseRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface KitchenRepositoryInterface extends BaseRepositoryInterface
{
    public function getKanbanTasks(): Collection;
    public function getPaginated(int $perPage = 15): LengthAwarePaginator;
    public function updateTaskStatus(int $taskId, string $status): bool;
    public function findById(int $id): ?KitchenTask;
}
