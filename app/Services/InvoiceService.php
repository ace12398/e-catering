<?php

namespace App\Services;

use App\Models\Invoice;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    public function __construct(
        protected InvoiceRepositoryInterface $invoiceRepo
    ) {}

    public function generateForOrder(array $data): Invoice
    {
        return $this->invoiceRepo->createInvoice($data);
    }

    public function verify(int $invoiceId, int $adminId): bool
    {
        return $this->invoiceRepo->verify($invoiceId, $adminId);
    }

    public function reject(int $invoiceId, int $adminId, string $reason = ''): bool
    {
        return $this->invoiceRepo->reject($invoiceId, $adminId, $reason);
    }

    public function uploadProof(int $invoiceId, UploadedFile $file, ?string $note = null): bool
    {
        // Hapus file lama jika ada
        $invoice = $this->invoiceRepo->findById($invoiceId);
        if ($invoice && $invoice->payment_proof) {
            Storage::disk('public')->delete($invoice->payment_proof);
        }

        $path = $file->store('payment_proofs/' . date('Y/m'), 'public');
        return $this->invoiceRepo->uploadProof($invoiceId, $path, $note);
    }
}
