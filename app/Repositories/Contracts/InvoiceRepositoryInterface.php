<?php

namespace App\Repositories\Contracts;

use App\Models\Invoice;
use App\Repositories\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface InvoiceRepositoryInterface extends BaseRepositoryInterface
{
    public function getPaginated(int $perPage = 10): LengthAwarePaginator;
    public function findById(int $id): ?Invoice;
    public function createInvoice(array $data): Invoice;
    public function verify(int $id, int $adminId): bool;
    public function reject(int $id, int $adminId, string $reason = ''): bool;
    public function uploadProof(int $id, string $path, ?string $note = null): bool;
    public function getByOrderId(int $orderId): ?Invoice;
}
