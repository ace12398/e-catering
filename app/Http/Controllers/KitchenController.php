<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\KitchenRepositoryInterface;
use App\Services\KitchenService;
use App\Models\WorkspacePreference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KitchenController extends Controller
{
    public function __construct(
        protected KitchenRepositoryInterface $kitchenRepo,
        protected KitchenService $kitchenService
    ) {}

    public function index(): View
    {
        $tasks = $this->kitchenRepo->getKanbanTasks();

        // Ambil layout_matrix user untuk customization
        $user        = auth()->user();
        $pref        = $user ? WorkspacePreference::where('user_id', $user->id)->first() : null;
        $layoutMatrix = $pref ? $pref->effective_layout_matrix : WorkspacePreference::getDefaultLayoutMatrix();
        $kitchenComponentStyles = $layoutMatrix['component_styles']['kitchen'] ?? WorkspacePreference::getDefaultLayoutMatrix()['component_styles']['kitchen'];
        $kitchenKpiOrder        = $layoutMatrix['kitchen_kpi_order'] ?? ['waiting', 'cooking', 'packing', 'done'];
        $kitchenPanelsOrder     = $layoutMatrix['kitchen_panels_order'] ?? ['k-list-waiting', 'k-list-cooking', 'k-list-packing', 'k-list-done'];

        return view('kitchen.board', compact('tasks', 'layoutMatrix', 'kitchenComponentStyles', 'kitchenKpiOrder', 'kitchenPanelsOrder'));
    }

    public function updateStatus(int $id, Request $request): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:waiting,cooking,packing,done'],
        ]);

        $task = $this->kitchenRepo->findById($id);
        abort_if(!$task, 404);

        $newStatus = $request->input('status');
        $this->kitchenService->updateStatus($task, $newStatus);

        $statusLabels = [
            'cooking' => 'Mulai memasak',
            'packing'  => 'Pengemasan dimulai',
            'done'     => 'Masakan selesai & siap dikirim',
        ];

        $message = $statusLabels[$newStatus] ?? 'Status berhasil diperbarui';

        return redirect()->route('kitchen.index')->with('success', $message . '.');
    }
}
