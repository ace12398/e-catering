<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\DeliveryRepositoryInterface;
use App\Services\DeliveryService;
use App\Models\WorkspacePreference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryController extends Controller
{
    public function __construct(
        protected DeliveryRepositoryInterface $deliveryRepo,
        protected DeliveryService $deliveryService
    ) {}

    public function index(): View
    {
        $deliveries = $this->deliveryRepo->getPaginated(15);

        // Ambil layout_matrix user untuk customization
        $user        = auth()->user();
        $pref        = $user ? WorkspacePreference::where('user_id', $user->id)->first() : null;
        $layoutMatrix = $pref ? $pref->effective_layout_matrix : WorkspacePreference::getDefaultLayoutMatrix();
        $deliveryComponentStyles = $layoutMatrix['component_styles']['delivery'] ?? WorkspacePreference::getDefaultLayoutMatrix()['component_styles']['delivery'];
        $deliveryKpiOrder        = $layoutMatrix['delivery_kpi_order'] ?? ['waiting', 'on_delivery', 'done', 'total'];
        $deliveryItemsOrder      = $layoutMatrix['delivery_items_order'] ?? [];

        // Terapkan urutan custom item pengiriman pada koleksi halaman ini tanpa merusak pagination
        if (!empty($deliveryItemsOrder)) {
            $orderMap = array_flip($deliveryItemsOrder);
            $sortedItems = $deliveries->getCollection()->sortBy(function ($item) use ($orderMap) {
                return $orderMap[$item->id] ?? 999999;
            })->values();
            $deliveries->setCollection($sortedItems);
        }

        return view('delivery.index', compact('deliveries', 'layoutMatrix', 'deliveryComponentStyles', 'deliveryKpiOrder', 'deliveryItemsOrder'));
    }

    public function updateStatus(int $id, Request $request): RedirectResponse
    {
        $request->validate([
            'action' => ['required', 'string', 'in:pickup,complete'],
        ]);

        $delivery = $this->deliveryRepo->findById($id);
        abort_if(!$delivery, 404);

        $action = $request->input('action');
        $this->deliveryService->updateStatus($delivery, $action);

        $messages = [
            'pickup'   => 'Pesanan berhasil diambil. Status pengiriman: Dalam Perjalanan.',
            'complete' => 'Pesanan berhasil diterima. Status: Selesai.',
        ];

        return redirect()->route('delivery.index')
            ->with('success', $messages[$action] ?? 'Status pengiriman diperbarui.');
    }
}
