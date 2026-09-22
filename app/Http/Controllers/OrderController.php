<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateOrderRequest;
use App\Repositories\Contracts\InvoiceRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Services\InvoiceService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepo,
        protected OrderService $orderService,
        protected InvoiceRepositoryInterface $invoiceRepo,
        protected InvoiceService $invoiceService
    ) {}

    public function index(Request $request): View
    {
        $user   = $request->user();
        $status = $request->query('status');
        $search = $request->query('search');

        $query = $user->isAdmin()
            ? \App\Models\Order::with(['user.profile', 'items.menu', 'kitchenTask', 'delivery', 'invoice'])
            : \App\Models\Order::where('user_id', $user->id)->with(['items.menu', 'kitchenTask', 'delivery', 'invoice']);

        if (!empty($status) && $status !== 'all') {
            if ($status === 'pending') {
                $query->whereIn('status', ['pending', 'menunggu_pembayaran']);
            } elseif ($status === 'preparing') {
                $query->whereIn('status', ['preparing', 'processing', 'sedang_diproses', 'sedang_dimasak']);
            } elseif ($status === 'on_delivery') {
                $query->whereIn('status', ['on_delivery', 'sedang_dikirim']);
            } elseif ($status === 'completed') {
                $query->whereIn('status', ['completed', 'selesai']);
            } else {
                $query->where('status', $status);
            }
        }

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('delivery_address', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        $preference = \App\Models\WorkspacePreference::firstOrCreate(
            ['user_id' => $user->id],
            ['preset' => 'institution_organization']
        );

        $layoutMatrix     = $preference->effective_layout_matrix;
        $ordersStyles        = $layoutMatrix['component_styles']['orders'] ?? $defaultMatrix['component_styles']['orders'];
        $ordersSectionsOrder = $layoutMatrix['orders_sections_order'] ?? ['O-HEADER', 'O-KPI', 'O-FILTER', 'O-LIST', 'O-PAGINATION'];
        $ordersKpiOrder      = $layoutMatrix['orders_kpi_order'] ?? ['pending', 'preparing', 'on_delivery', 'completed'];
        $ordersItemsOrder    = $layoutMatrix['orders_items_order'] ?? [];

        if (!empty($ordersItemsOrder)) {
            $sortedItems = $orders->getCollection()->sortBy(function ($order) use ($ordersItemsOrder) {
                $pos = array_search($order->id, $ordersItemsOrder);
                return $pos === false ? 999999 : $pos;
            })->values();
            $orders->setCollection($sortedItems);
        }

        return view('orders.index', compact('orders', 'layoutMatrix', 'preference', 'ordersStyles', 'ordersSectionsOrder', 'ordersKpiOrder', 'ordersItemsOrder'));
    }

    public function show(int $id): View
    {
        $order = $this->orderRepo->findById($id);
        abort_if(!$order, 404);

        // Ensure only the owner or admin can see
        $user = auth()->user();
        if (!$user->isAdmin() && $order->user_id !== $user->id) {
            abort(403);
        }

        return view('orders.show', compact('order'));
    }

    public function store(CreateOrderRequest $request): RedirectResponse
    {
        try {
            $order = $this->orderService->createFromCart($request->user(), $request->validated());
            return redirect()->route('orders.show', $order->id)
                ->with('success', 'Pesanan berhasil dibuat! Silakan lakukan pembayaran.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return redirect()->route('cart.index')
                ->with('error', 'Terjadi kesalahan saat memproses pesanan: ' . $e->getMessage());
        }
    }

    public function uploadProof(int $id, Request $request): RedirectResponse
    {
        $request->validate([
            'payment_proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf,webp', 'max:5120'],
            'payment_note'  => ['nullable', 'string', 'max:500'],
        ]);

        $order = $this->orderRepo->findById($id);
        abort_if(!$order, 404);

        // Only the order owner can upload proof
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Akses ditolak.');
        }

        $invoice = $this->invoiceRepo->getByOrderId($id);
        if (!$invoice) {
            return back()->with('error', 'Invoice tidak ditemukan untuk pesanan ini.');
        }

        $result = $this->invoiceService->uploadProof(
            $invoice->id,
            $request->file('payment_proof'),
            $request->input('payment_note')
        );

        if ($result) {
            return redirect()->route('orders.show', $id)
                ->with('success', 'Bukti pembayaran berhasil dikirim. Menunggu verifikasi admin.');
        }

        return back()->with('error', 'Gagal mengunggah bukti pembayaran. Coba lagi.');
    }

    public function cancel(int $id): RedirectResponse
    {
        $order = $this->orderRepo->findById($id);
        abort_if(!$order, 404);

        $user = auth()->user();
        if ($order->user_id !== $user->id && !$user->isAdmin()) {
            abort(403);
        }

        $currentStatus = is_object($order->status) ? $order->status->value : $order->status;
        $cancellableStatuses = ['menunggu_pembayaran', 'menunggu_verifikasi', 'pending'];

        if (!in_array($currentStatus, $cancellableStatuses)) {
            return back()->with('error', 'Pesanan tidak dapat dibatalkan karena sudah diproses.');
        }

        $order->update(['status' => 'dibatalkan']);
        $order->invoice?->update(['status' => 'ditolak']);

        return redirect()->route('orders.index')
            ->with('success', 'Pesanan berhasil dibatalkan.');
    }
}
