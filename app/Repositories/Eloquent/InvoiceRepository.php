<?php

namespace App\Repositories\Eloquent;

use App\Models\Invoice;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class InvoiceRepository extends BaseRepository implements InvoiceRepositoryInterface
{
    public function __construct(Invoice $model)
    {
        parent::__construct($model);
    }

    public function getPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return $this->model->with(['order', 'customer'])->latest()->paginate($perPage);
    }

    public function findById(int $id): ?Invoice
    {
        return $this->model->with(['order', 'customer'])->find($id);
    }

    public function createInvoice(array $data): Invoice
    {
        return $this->model->create($data);
    }

    public function verify(int $id, int $adminId): bool
    {
        $invoice = $this->findById($id);
        if (!$invoice) return false;
        
        return DB::transaction(function() use ($invoice, $adminId) {
            $invoice->update([
                'status' => 'lunas',
                'paid_at' => now(),
                'verified_at' => now(),
                'verified_by' => $adminId,
            ]);
            // Update order status
            $invoice->order?->update(['status' => 'sedang_diproses', 'payment_status' => 'lunas']);
            return true;
        });
    }

    public function reject(int $id, int $adminId, string $reason = ''): bool
    {
        $invoice = $this->findById($id);
        if (!$invoice) return false;
        
        return DB::transaction(function() use ($invoice, $adminId, $reason) {
            $invoice->update([
                'status' => 'ditolak',
                'verified_at' => now(),
                'verified_by' => $adminId,
                'payment_note' => $reason ?: 'Bukti pembayaran tidak valid.',
            ]);
            $invoice->order?->update(['status' => 'menunggu_pembayaran', 'payment_status' => 'belum_dibayar']);
            return true;
        });
    }

    public function uploadProof(int $id, string $path, ?string $note = null): bool
    {
        $invoice = $this->findById($id);
        if (!$invoice) return false;
        
        return DB::transaction(function() use ($invoice, $path, $note) {
            $invoice->update([
                'payment_proof' => $path,
                'payment_note' => $note,
                'status' => 'menunggu_verifikasi',
            ]);
            $invoice->order?->update(['status' => 'menunggu_verifikasi', 'payment_status' => 'menunggu_verifikasi']);
            return true;
        });
    }

    public function getByOrderId(int $orderId): ?Invoice
    {
        return $this->model->where('order_id', $orderId)->first();
    }
}
