<?php

namespace App\Http\Controllers;

use App\Models\WorkspacePreference;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceRepositoryInterface $invoiceRepo,
        protected InvoiceService $invoiceService,
        protected OrderRepositoryInterface $orderRepo
    ) {}

    public function index(Request $request): View
    {
        $invoices = $this->invoiceRepo->getPaginated(15);

        $preference = WorkspacePreference::firstOrCreate(
            ['user_id' => $request->user()->id],
            ['preset' => 'institution_organization']
        );
        $layoutMatrix    = $preference->effective_layout_matrix;
        $defaultKpiOrder = ['lunas', 'menunggu', 'belum_bayar', 'ditolak'];
        $savedOrder      = $layoutMatrix['finance_kpi_order'] ?? $defaultKpiOrder;
        $financeKpiOrder = array_values(array_unique(array_merge(
            is_array($savedOrder) ? $savedOrder : [],
            $defaultKpiOrder
        )));
        $financeComponentStyles = $layoutMatrix['component_styles']['finance']
            ?? WorkspacePreference::getDefaultLayoutMatrix()['component_styles']['finance'];

        return view('finance.index', compact('invoices', 'financeKpiOrder', 'layoutMatrix', 'financeComponentStyles'));
    }

    public function show(int $id): View
    {
        $invoice = $this->invoiceRepo->findById($id);
        abort_if(!$invoice, 404);
        return view('finance.show', compact('invoice'));
    }

    public function verify(int $id): RedirectResponse
    {
        $invoice = $this->invoiceRepo->findById($id);
        abort_if(!$invoice, 404);

        $result = $this->invoiceService->verify($id, auth()->id());

        if ($result) {
            return redirect()->route('finance.index')
                ->with('success', 'Pembayaran berhasil diverifikasi. Pesanan akan segera diproses.');
        }

        return back()->with('error', 'Gagal memverifikasi pembayaran. Coba lagi.');
    }

    public function reject(int $id, Request $request): RedirectResponse
    {
        $invoice = $this->invoiceRepo->findById($id);
        abort_if(!$invoice, 404);

        $reason = $request->input('reason', 'Bukti pembayaran tidak sesuai atau tidak valid.');
        $result = $this->invoiceService->reject($id, auth()->id(), $reason);

        if ($result) {
            return redirect()->route('finance.index')
                ->with('success', 'Bukti pembayaran ditolak. Customer akan diberitahu.');
        }

        return back()->with('error', 'Gagal menolak pembayaran. Coba lagi.');
    }
}
