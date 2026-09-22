<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    public function all(array $columns = ['*']): Collection;

    public function find(int|string $id, array $columns = ['*']): ?Model;

    public function create(array $data): Model;

    public function update(int|string $id, array $data): bool;

    public function delete(int|string $id): bool;
}
