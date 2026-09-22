<x-dashboard-layout>
    <x-slot name="title">Manajemen Dapur Katering — E-Catering</x-slot>
    <x-slot name="toolbarTitle">Manajemen Dapur</x-slot>

    {{-- SortableJS untuk KPI Stats & Kanban List Panels Reorder --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

    @php
        $effectiveMatrix = $layoutMatrix ?? auth()->user()?->preference?->effective_layout_matrix ?? \App\Models\WorkspacePreference::getDefaultLayoutMatrix();
        $kitchenSavedStyles = $kitchenComponentStyles ?? $effectiveMatrix['component_styles']['kitchen'] ?? [];
        $defaultKitchenStyles = \App\Models\WorkspacePreference::getDefaultLayoutMatrix()['component_styles']['kitchen'];
        $kitchenKpiOrder = $kitchenKpiOrder ?? $effectiveMatrix['kitchen_kpi_order'] ?? ['waiting', 'cooking', 'packing', 'done'];
        $kitchenPanelsOrder = $kitchenPanelsOrder ?? $effectiveMatrix['kitchen_panels_order'] ?? ['k-list-waiting', 'k-list-cooking', 'k-list-packing', 'k-list-done'];

        // Definisi master data stats & list panels
        $allStats = [
            'waiting' => ['id'=>'waiting', 'label'=>'Menunggu',   'statuses'=>['waiting'], 'color'=>'slate',   'icon'=>'⏳'],
            'cooking' => ['id'=>'cooking', 'label'=>'Memasak',    'statuses'=>['cooking'], 'color'=>'amber',   'icon'=>'🔥'],
            'packing' => ['id'=>'packing', 'label'=>'Pengemasan', 'statuses'=>['packing'], 'color'=>'purple',  'icon'=>'📦'],
            'done'    => ['id'=>'done',    'label'=>'Selesai',    'statuses'=>['done'],    'color'=>'emerald', 'icon'=>'✅'],
        ];

        $allPanels = [
            'k-list-waiting' => [
                'id' => 'k-list-waiting',
                'title' => '⏳ Menunggu',
                'status_key' => 'waiting',
                'color' => 'slate',
                'empty_icon' => '✅',
                'empty_text' => 'Antrean kosong',
            ],
            'k-list-cooking' => [
                'id' => 'k-list-cooking',
                'title' => '🔥 Memasak',
                'status_key' => 'cooking',
                'color' => 'amber',
                'empty_icon' => '🍳',
                'empty_text' => 'Tidak ada proses masak',
            ],
            'k-list-packing' => [
                'id' => 'k-list-packing',
                'title' => '📦 Pengemasan',
                'status_key' => 'packing',
                'color' => 'purple',
                'empty_icon' => '📫',
                'empty_text' => 'Tidak ada pengemasan',
            ],
            'k-list-done' => [
                'id' => 'k-list-done',
                'title' => '✅ Selesai',
                'status_key' => 'done',
                'color' => 'emerald',
                'empty_icon' => '🚀',
                'empty_text' => 'Belum ada yang selesai',
            ],
        ];

        // Susun urutan sesuai preference
        $sortedStats = [];
        foreach ($kitchenKpiOrder as $kId) {
            if (isset($allStats[$kId])) $sortedStats[] = $allStats[$kId];
        }
        foreach ($allStats as $kId => $data) {
            if (!in_array($kId, $kitchenKpiOrder)) $sortedStats[] = $data;
        }

        $sortedPanels = [];
        foreach ($kitchenPanelsOrder as $pId) {
            if (isset($allPanels[$pId])) $sortedPanels[] = $allPanels[$pId];
        }
        foreach ($allPanels as $pId => $data) {
            if (!in_array($pId, $kitchenPanelsOrder)) $sortedPanels[] = $data;
        }
    @endphp

    {{-- STYLE OVERRIDES FOR SORTABLE DROPZONE AND EDIT STATE --}}
    <style>
        .dashboard-sortable-ghost {
            opacity: 0.35 !important;
            background: rgba(245, 158, 11, 0.12) !important;
            border: 2px dashed #f59e0b !important;
            border-radius: 1rem !important;
        }
        .dashboard-sortable-drag {
            opacity: 0.95 !important;
            transform: scale(1.02) rotate(1deg) !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 10px 10px -5px rgba(0, 0, 0, 0.1) !important;
        }
        .dashboard-sortable-chosen {
            cursor: grabbing !important;
        }
    </style>

    <div class="space-y-6 relative" id="kitchen-root">
        {{-- Flash Notification --}}
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold shadow-sm">✅ {{ session('success') }}</div>
        @endif

        {{-- TOAST NOTIFICATION --}}
        <div id="kitchen-toast" class="hidden fixed bottom-6 right-6 z-50 p-4 rounded-xl bg-slate-900 text-white shadow-2xl border border-slate-700 flex items-center space-x-3 transition-all transform translate-y-4 opacity-0">
            <span id="kitchen-toast-icon" class="text-amber-400 text-lg font-bold">✓</span>
            <span id="kitchen-toast-message" class="text-xs font-medium">Layout manajemen dapur berhasil diperbarui.</span>
        </div>

        {{-- CANONICAL ADAPTIVE MODE BANNER (MATCHING /finance & /dashboard) --}}
        <div id="kitchen-edit-bar" class="hidden sticky top-4 z-40 p-4 rounded-2xl bg-amber-950/90 dark:bg-amber-950/95 backdrop-blur border-2 border-amber-500 text-white shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center space-x-3">
                <span class="p-2.5 rounded-xl bg-amber-500/20 border border-amber-400/30 text-amber-300 font-mono text-base">✨</span>
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-300">Mode Penyesuaian Tampilan Aktif — Manajemen Dapur</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500 text-slate-950">Drag & Drop Aktif</span>
                    </div>
                    <p class="text-xs text-amber-100/90 mt-0.5">Ubah bentuk (shape), lebar, tinggi, dan geser kartu status serta kolom alur kerja dapur secara live.</p>
                </div>
            </div>
            <div class="flex items-center space-x-2 shrink-0">
                <button type="button" onclick="cancelKitchenLayout()" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs transition-colors border border-slate-600">✕ Batal</button>
                <button type="button" onclick="resetKitchenLayout()" class="px-3.5 py-2 rounded-xl bg-amber-900/80 hover:bg-amber-800 text-amber-200 font-semibold text-xs transition-colors border border-amber-600">🔄 Reset Default</button>
                <button type="button" onclick="saveKitchenLayout()" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold text-xs shadow-md transition-colors flex items-center space-x-1.5"><span>💾 Simpan Tampilan</span></button>
            </div>
        </div>

        {{-- Header Section --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Papan Antrean Dapur</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kelola status masakan dan update progres memasak secara langsung.</p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                    🔴 Live — {{ $tasks->count() }} Tugas
                </span>
                {{-- TOMBOL EDIT TAMPILAN DAPUR --}}
                <button type="button" id="kitchen-btn-edit" onclick="toggleKitchenEditMode()"
                        class="h-9 px-4 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs transition-colors flex items-center space-x-2 shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <span id="kitchen-btn-text">⚙️ Sesuaikan Tampilan</span>
                </button>
            </div>
        </div>

        {{-- 1. TOP SUMMARY STATS CARDS (SORTABLE & RESIZABLE) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-start" id="kitchen-stats-container">
            @foreach($sortedStats as $stat)
                @php $statId = $stat['id']; @endphp
                <div data-kpi-wrapper="{{ $statId }}" class="w-full flex flex-col items-center space-y-3 transition-all">
                    
                    {{-- BUSINESS PREVIEW CARD --}}
                    <div id="kitchen-stat-card-{{ $statId }}" data-component-id="{{ $statId }}" class="caterflow-visual-card w-full p-4 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm space-y-1 transition-all relative overflow-hidden flex flex-col justify-between">
                        
                        {{-- Dedicated Drag Handle Badge (Edit Mode Only) --}}
                        <div class="kitchen-kpi-drag-handle hidden absolute top-2 right-2 z-10 px-2 py-0.5 rounded-md bg-amber-500/20 hover:bg-amber-500 text-amber-800 dark:text-amber-200 hover:text-white border border-amber-400/40 cursor-grab active:cursor-grabbing transition-colors flex items-center space-x-1 select-none shadow-sm"
                             title="Tahan dan geser untuk memindahkan urutan kartu status">
                            <svg class="w-3 h-3 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 8h16M4 16h16"></path>
                            </svg>
                            <span class="text-[9px] font-extrabold font-mono pointer-events-none">GESER</span>
                        </div>

                        <div class="flex items-center justify-between flex-between-header">
                            <span class="text-[11px] font-semibold text-slate-500 uppercase adaptive-label flex items-center space-x-1">
                                <span class="adaptive-icon text-sm">{{ $stat['icon'] }}</span>
                                <span>{{ $stat['label'] }}</span>
                            </span>
                        </div>
                        <div class="text-2xl font-extrabold text-{{ $stat['color'] }}-600 dark:text-{{ $stat['color'] }}-400 stat-value">
                            {{ $tasks->whereIn('status', $stat['statuses'])->count() }}
                        </div>
                    </div>

                    {{-- SEPARATE RECTANGULAR CONFIGURATION EDITOR (OUTSIDE PREVIEW) --}}
                    <div class="kitchen-edit-controls hidden w-full p-3.5 rounded-xl bg-slate-900/90 text-white border border-slate-700/60 shadow-lg space-y-2.5 text-xs backdrop-blur z-20">
                        <div class="flex items-center justify-between font-bold border-b border-slate-700 pb-1.5 text-[10px]">
                            <div class="flex items-center space-x-1.5">
                                <span class="kitchen-kpi-drag-handle cursor-grab active:cursor-grabbing px-2 py-0.5 rounded bg-amber-600 hover:bg-amber-500 text-white text-[10px] font-bold flex items-center space-x-1 shadow-sm transition-colors select-none"
                                      title="Tahan dan geser untuk memindahkan urutan">
                                    <span>⠿</span>
                                    <span>Geser</span>
                                </span>
                                <span class="text-amber-400 font-bold">Atur: {{ $stat['label'] }}</span>
                            </div>
                            <button type="button" onclick="resetSingleKitchenComponent('{{ $statId }}')" class="px-1.5 py-0.5 rounded text-[9px] font-semibold bg-amber-800/80 hover:bg-amber-700 text-white border border-amber-600 transition-colors" title="Reset komponen ini">🔄 Reset</button>
                        </div>
                        <div class="space-y-1">
                            <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Bentuk</span>
                            <div class="grid grid-cols-3 gap-1">
                                @foreach(['rectangle'=>'■ Kotak','rounded'=>'◉ Bulat','sharp'=>'▪ Tajam','pill'=>'⬭ Pil','circle'=>'○ Lingkar','hexagon'=>'⬡ Hex'] as $sId => $sLabel)
                                    <button type="button" onclick="setKitchenComponentShape('{{ $statId }}', '{{ $sId }}')" class="k-shape-btn-{{ $statId }} px-1 py-1 rounded text-[9px] font-semibold border text-center transition-colors bg-white text-slate-800 border-slate-300 hover:bg-amber-500 hover:text-white" data-shape="{{ $sId }}">{{ $sLabel }}</button>
                                @endforeach
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-1.5">
                            <div>
                                <div class="flex items-center justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                                    <span>Lebar</span>
                                    <span id="k-wval-{{ $statId }}" class="text-amber-400 font-mono font-bold">240px</span>
                                </div>
                                <input type="range" min="140" max="400" step="10" value="240" id="k-width-slider-{{ $statId }}" oninput="onKitchenSliderInput('{{ $statId }}', 'width', this.value)" class="w-full accent-amber-500">
                            </div>
                            <div>
                                <div class="flex items-center justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                                    <span>Tinggi</span>
                                    <span id="k-hval-{{ $statId }}" class="text-amber-400 font-mono font-bold">120px</span>
                                </div>
                                <input type="range" min="70" max="240" step="10" value="120" id="k-height-slider-{{ $statId }}" oninput="onKitchenSliderInput('{{ $statId }}', 'height', this.value)" class="w-full accent-amber-500">
                            </div>
                        </div>
                        <div class="text-[9px] font-mono text-amber-400 pt-1 border-t border-slate-700/60" id="k-size-display-{{ $statId }}">240 × 120 px</div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- 2. LOWER KANBAN LIST PANELS (SORTABLE & RESIZABLE) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 items-start" id="kitchen-kanban-container">
            @foreach($sortedPanels as $listComp)
                @php
                    $panelId   = $listComp['id'];
                    $compTasks = $tasks->where('status', $listComp['status_key']);
                @endphp
                <div data-panel-wrapper="{{ $panelId }}" class="w-full flex flex-col items-center space-y-3 transition-all">
                    
                    {{-- BUSINESS PREVIEW CARD FOR KANBAN COLUMN --}}
                    <div id="kitchen-stat-card-{{ $panelId }}" data-component-id="{{ $panelId }}"
                         class="caterflow-visual-card p-4 rounded-2xl bg-{{ $listComp['color'] === 'slate' ? 'slate-100 dark:bg-slate-900' : $listComp['color'].'-50 dark:bg-'.$listComp['color'].'-950/20' }} border border-{{ $listComp['color'] === 'slate' ? 'slate-200 dark:border-slate-800' : $listComp['color'].'-200 dark:border-'.$listComp['color'].'-800/50' }} space-y-3 font-sans transition-all duration-300 w-full overflow-hidden relative">
                        
                        {{-- Dedicated Drag Handle Badge (Edit Mode Only) --}}
                        <div class="kitchen-panel-drag-handle hidden absolute top-2 right-2 z-10 px-2 py-0.5 rounded-md bg-amber-500/20 hover:bg-amber-500 text-amber-800 dark:text-amber-200 hover:text-white border border-amber-400/40 cursor-grab active:cursor-grabbing transition-colors flex items-center space-x-1 select-none shadow-sm"
                             title="Tahan dan geser untuk memindahkan urutan kolom">
                            <svg class="w-3 h-3 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 8h16M4 16h16"></path>
                            </svg>
                            <span class="text-[9px] font-extrabold font-mono pointer-events-none">GESER</span>
                        </div>

                        <div class="flex items-center justify-between flex-between-header">
                            <h3 class="text-xs font-bold text-{{ $listComp['color'] === 'slate' ? 'slate-700 dark:text-slate-300' : $listComp['color'].'-700 dark:text-'.$listComp['color'].'-400' }} uppercase adaptive-label">{{ $listComp['title'] }}</h3>
                            <span class="text-xs font-bold bg-{{ $listComp['color'] === 'slate' ? 'slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300' : $listComp['color'].'-200 dark:bg-'.$listComp['color'].'-800 text-'.$listComp['color'].'-800 dark:text-'.$listComp['color'].'-200' }} px-2 py-0.5 rounded-full badge-adaptive">
                                {{ $compTasks->count() }}
                            </span>
                        </div>

                        {{-- TASKS LIST LOOP (OPERATIONAL WORKFLOW) --}}
                        <div class="space-y-2.5 overflow-y-auto max-h-[420px] pr-1 w-full">
                            @forelse($compTasks as $task)
                                <div class="p-3.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm space-y-2.5">
                                    <div class="flex items-center justify-between flex-between-header">
                                        <span class="text-xs font-bold text-slate-900 dark:text-white truncate stat-value">{{ $task->order?->order_number ?? 'Pesanan' }}</span>
                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-{{ $task->priority === 'rush' ? 'rose' : ($task->priority === 'high' ? 'amber' : 'slate') }}-100 text-{{ $task->priority === 'rush' ? 'rose' : ($task->priority === 'high' ? 'amber' : 'slate') }}-700 badge-adaptive">
                                            {{ strtoupper($task->priority ?? 'normal') }}
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 space-y-0.5 subtext">
                                        @if($listComp['status_key'] === 'waiting')
                                            <p>Stasiun: {{ $task->station ?? 'Dapur Utama' }}</p>
                                            <p>Masuk: {{ $task->created_at?->format('d M, H:i') }}</p>
                                            @if($task->order)
                                                <p>{{ $task->order->items->count() }} item pesanan</p>
                                            @endif
                                        @elseif($listComp['status_key'] === 'cooking')
                                            <p>Chef: {{ $task->chef?->name ?? 'Tidak Ditentukan' }}</p>
                                            <p>Mulai: {{ $task->started_at?->format('H:i') ?? '-' }} WIB</p>
                                        @elseif($listComp['status_key'] === 'packing')
                                            <p>Pengemasan berlangsung...</p>
                                            @if($task->order)
                                                <p>Alamat: {{ Str::limit($task->order->delivery_address ?? '-', 30) }}</p>
                                            @endif
                                        @elseif($listComp['status_key'] === 'done')
                                            <p class="text-emerald-600 font-semibold">✅ Siap untuk dikirim!</p>
                                            <p>Selesai: {{ $task->finished_at?->format('H:i') ?? '-' }} WIB</p>
                                        @endif
                                    </div>
                                    @if($listComp['status_key'] !== 'done')
                                        <form method="POST" action="{{ route('kitchen.update-status', $task->id) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="{{ $listComp['status_key'] === 'waiting' ? 'cooking' : ($listComp['status_key'] === 'cooking' ? 'packing' : 'done') }}">
                                            <button type="submit" class="w-full py-1.5 rounded-lg bg-{{ $listComp['status_key'] === 'waiting' ? 'amber' : ($listComp['status_key'] === 'cooking' ? 'purple' : 'emerald') }}-500 hover:bg-{{ $listComp['status_key'] === 'waiting' ? 'amber' : ($listComp['status_key'] === 'cooking' ? 'purple' : 'emerald') }}-600 text-white font-bold text-[11px] transition-colors btn-adaptive shadow-sm">
                                                @if($listComp['status_key'] === 'waiting')
                                                    🔥 Mulai Memasak
                                                @elseif($listComp['status_key'] === 'cooking')
                                                    📦 Mulai Pengemasan
                                                @else
                                                    ✅ Selesai & Siap Kirim
                                                @endif
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @empty
                                <div class="text-center py-6 text-[11px] text-slate-400 subtext">
                                    <div class="text-xl mb-1">{{ $listComp['empty_icon'] }}</div>
                                    {{ $listComp['empty_text'] }}
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- SEPARATE RECTANGULAR CONFIGURATION EDITOR (OUTSIDE PREVIEW) --}}
                    <div class="kitchen-edit-controls hidden w-full p-3.5 rounded-xl bg-slate-900/90 text-white border border-slate-700/60 shadow-lg space-y-2.5 text-xs backdrop-blur z-20">
                        <div class="flex items-center justify-between font-bold border-b border-slate-700 pb-1.5 text-[10px]">
                            <div class="flex items-center space-x-1.5">
                                <span class="kitchen-panel-drag-handle cursor-grab active:cursor-grabbing px-2 py-0.5 rounded bg-amber-600 hover:bg-amber-500 text-white text-[10px] font-bold flex items-center space-x-1 shadow-sm transition-colors select-none"
                                      title="Tahan dan geser untuk memindahkan urutan">
                                    <span>⠿</span>
                                    <span>Geser</span>
                                </span>
                                <span class="text-amber-400 font-bold">Atur: {{ $listComp['title'] }}</span>
                            </div>
                            <button type="button" onclick="resetSingleKitchenComponent('{{ $panelId }}')" class="px-1.5 py-0.5 rounded text-[9px] font-semibold bg-amber-800/80 hover:bg-amber-700 text-white border border-amber-600 transition-colors" title="Reset panel ini">🔄 Reset</button>
                        </div>
                        <div class="space-y-1">
                            <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Bentuk</span>
                            <div class="grid grid-cols-3 gap-1">
                                @foreach(['rectangle'=>'■ Kotak','rounded'=>'◉ Bulat','sharp'=>'▪ Tajam','pill'=>'⬭ Pil','circle'=>'○ Lingkar','hexagon'=>'⬡ Hex'] as $sId => $sLabel)
                                    <button type="button" onclick="setKitchenComponentShape('{{ $panelId }}', '{{ $sId }}')" class="k-shape-btn-{{ $panelId }} px-1 py-1 rounded text-[9px] font-semibold border text-center transition-colors bg-white text-slate-800 border-slate-300 hover:bg-amber-500 hover:text-white" data-shape="{{ $sId }}">{{ $sLabel }}</button>
                                @endforeach
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-1.5">
                            <div>
                                <div class="flex items-center justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                                    <span>Lebar</span>
                                    <span id="k-wval-{{ $panelId }}" class="text-amber-400 font-mono font-bold">320px</span>
                                </div>
                                <input type="range" min="180" max="600" step="10" value="320" id="k-width-slider-{{ $panelId }}" oninput="onKitchenSliderInput('{{ $panelId }}', 'width', this.value)" class="w-full accent-amber-500">
                            </div>
                            <div>
                                <div class="flex items-center justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                                    <span>Tinggi</span>
                                    <span id="k-hval-{{ $panelId }}" class="text-amber-400 font-mono font-bold">380px</span>
                                </div>
                                <input type="range" min="200" max="600" step="10" value="380" id="k-height-slider-{{ $panelId }}" oninput="onKitchenSliderInput('{{ $panelId }}', 'height', this.value)" class="w-full accent-amber-500">
                            </div>
                        </div>
                        <div class="text-[9px] font-mono text-amber-400 pt-1 border-t border-slate-700/60" id="k-size-display-{{ $panelId }}">320 × 380 px</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- KITCHEN CUSTOMIZATION ENGINE (CANONICAL MASTER /finance) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.kitchenIsEditMode       = false;
            window.kitchenKpiOrder         = @json($kitchenKpiOrder);
            window.kitchenKpiLastSaved     = [...window.kitchenKpiOrder];
            window.kitchenPanelsOrder      = @json($kitchenPanelsOrder);
            window.kitchenPanelsLastSaved  = [...window.kitchenPanelsOrder];
            window.kitchenSavedStyles      = @json($kitchenSavedStyles);
            window.kitchenDefaultStyles    = @json($defaultKitchenStyles);
            window.kitchenCurrentStyles    = Object.assign({}, window.kitchenDefaultStyles, window.kitchenSavedStyles);
            window.kitchenLastSavedStyles  = JSON.parse(JSON.stringify(window.kitchenCurrentStyles));
            window.kitchenKpiSortable      = null;
            window.kitchenPanelsSortable   = null;

            initKitchenSortables();
            initKitchenComponentStyles();
        });

        function initKitchenSortables() {
            // 1. Sortable for Top KPI Stats Cards
            const statsContainer = document.getElementById('kitchen-stats-container');
            if (statsContainer) {
                window.kitchenKpiSortable = new Sortable(statsContainer, {
                    animation: 200,
                    handle: '.kitchen-kpi-drag-handle',
                    ghostClass: 'dashboard-sortable-ghost',
                    dragClass: 'dashboard-sortable-drag',
                    chosenClass: 'dashboard-sortable-chosen',
                    disabled: true,
                    onEnd: function() {
                        const newOrder = [];
                        statsContainer.querySelectorAll('[data-kpi-wrapper]').forEach(el => newOrder.push(el.dataset.kpiWrapper));
                        window.kitchenKpiOrder = newOrder;
                    }
                });
            }

            // 2. Sortable for Lower Kanban List Panels
            const kanbanContainer = document.getElementById('kitchen-kanban-container');
            if (kanbanContainer) {
                window.kitchenPanelsSortable = new Sortable(kanbanContainer, {
                    animation: 200,
                    handle: '.kitchen-panel-drag-handle',
                    ghostClass: 'dashboard-sortable-ghost',
                    dragClass: 'dashboard-sortable-drag',
                    chosenClass: 'dashboard-sortable-chosen',
                    disabled: true,
                    onEnd: function() {
                        const newOrder = [];
                        kanbanContainer.querySelectorAll('[data-panel-wrapper]').forEach(el => newOrder.push(el.dataset.panelWrapper));
                        window.kitchenPanelsOrder = newOrder;
                    }
                });
            }
        }

        function initKitchenComponentStyles() {
            Object.keys(window.kitchenCurrentStyles).forEach(id => {
                applyKitchenComponentStyle(id, window.kitchenCurrentStyles[id]);
            });
        }

        function toggleKitchenEditMode() {
            window.kitchenIsEditMode = !window.kitchenIsEditMode;
            const bar  = document.getElementById('kitchen-edit-bar');
            const btn  = document.getElementById('kitchen-btn-text');
            const root = document.getElementById('kitchen-root');

            if (window.kitchenIsEditMode) {
                bar.classList.remove('hidden');
                btn.textContent = '✕ Keluar Mode Penyesuaian';
                root.classList.add('in-edit-mode');
                document.querySelectorAll('.kitchen-edit-controls').forEach(el => el.classList.remove('hidden'));
                document.querySelectorAll('.kitchen-kpi-drag-handle').forEach(el => el.classList.remove('hidden'));
                document.querySelectorAll('.kitchen-panel-drag-handle').forEach(el => el.classList.remove('hidden'));
                document.querySelectorAll('[data-kpi-wrapper], [data-panel-wrapper]').forEach(el => {
                    el.classList.add('p-1.5', 'rounded-2xl', 'border-2', 'border-dashed', 'border-amber-500/50', 'bg-amber-50/10');
                });
            } else {
                bar.classList.add('hidden');
                btn.textContent = '⚙️ Sesuaikan Tampilan';
                root.classList.remove('in-edit-mode');
                document.querySelectorAll('.kitchen-edit-controls').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('.kitchen-kpi-drag-handle').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('.kitchen-panel-drag-handle').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('[data-kpi-wrapper], [data-panel-wrapper]').forEach(el => {
                    el.classList.remove('p-1.5', 'rounded-2xl', 'border-2', 'border-dashed', 'border-amber-500/50', 'bg-amber-50/10');
                });
            }

            if (window.kitchenKpiSortable) window.kitchenKpiSortable.option('disabled', !window.kitchenIsEditMode);
            if (window.kitchenPanelsSortable) window.kitchenPanelsSortable.option('disabled', !window.kitchenIsEditMode);
            if (window.kitchenIsEditMode) updateKitchenStyleDisplays();
        }

        function setKitchenComponentShape(cId, shape) {
            if (!window.kitchenCurrentStyles[cId]) window.kitchenCurrentStyles[cId] = {};
            window.kitchenCurrentStyles[cId].shape = shape;
            applyKitchenComponentStyle(cId, window.kitchenCurrentStyles[cId]);

            document.querySelectorAll('.k-shape-btn-' + cId).forEach(btn => {
                const isActive = btn.dataset.shape === shape;
                btn.classList.toggle('bg-amber-500',   isActive);
                btn.classList.toggle('text-white',       isActive);
                btn.classList.toggle('border-amber-600', isActive);
                btn.classList.toggle('bg-white',        !isActive);
                btn.classList.toggle('text-slate-800',  !isActive);
                btn.classList.toggle('border-slate-300',!isActive);
            });
        }

        function onKitchenSliderInput(cId, dim, value) {
            const v = parseInt(value);
            if (isNaN(v)) return;
            if (!window.kitchenCurrentStyles[cId]) window.kitchenCurrentStyles[cId] = {};
            window.kitchenCurrentStyles[cId][dim] = v;
            applyKitchenComponentStyle(cId, window.kitchenCurrentStyles[cId]);
        }

        function applyKitchenComponentStyle(cId, style) {
            if (!style) return;
            const visualCard = document.querySelector('[data-component-id="' + cId + '"]');
            if (!visualCard) return;

            const isPanel   = cId.startsWith('k-list-');
            const defaultW  = isPanel ? 320 : 240;
            const defaultH  = isPanel ? 380 : 120;
            const shape     = style.shape || 'rectangle';

            const reqWidth  = parseInt(style.width)  || defaultW;
            const reqHeight = parseInt(style.height) || defaultH;

            const bounds = isPanel
                ? { minW: 180, maxW: 600, minH: 200, maxH: 600 }
                : { minW: 140, maxW: 400, minH: 70,  maxH: 240 };

            const clampedW = Math.max(bounds.minW, Math.min(bounds.maxW, reqWidth));
            const clampedH = Math.max(bounds.minH, Math.min(bounds.maxH, reqHeight));

            visualCard.style.maxWidth   = '';
            visualCard.style.maxHeight  = '';
            visualCard.style.minWidth   = '';
            visualCard.style.minHeight  = '';
            visualCard.style.boxSizing  = 'border-box';
            visualCard.style.clipPath   = '';
            visualCard.style.aspectRatio= '';
            visualCard.style.borderRadius = '';
            visualCard.style.alignSelf  = '';

            if (shape === 'circle') {
                const circleDiameter = Math.min(clampedW, clampedH);
                visualCard.style.width        = `${circleDiameter}px`;
                visualCard.style.height       = `${circleDiameter}px`;
                visualCard.style.minWidth     = `${circleDiameter}px`;
                visualCard.style.maxWidth     = `${circleDiameter}px`;
                visualCard.style.minHeight    = `${circleDiameter}px`;
                visualCard.style.maxHeight    = `${circleDiameter}px`;
                visualCard.style.aspectRatio  = '1 / 1';
                visualCard.style.borderRadius = '50%';
                visualCard.style.margin       = '0 auto';
                visualCard.style.alignSelf    = 'center';
            } else {
                visualCard.style.width     = `${clampedW}px`;
                visualCard.style.minHeight = `${clampedH}px`;
                visualCard.style.height    = `${clampedH}px`;
                visualCard.style.maxWidth  = '100%';
                visualCard.style.margin    = '';

                switch (shape) {
                    case 'rounded':
                        visualCard.style.borderRadius = '24px';
                        break;
                    case 'pill':
                        visualCard.style.borderRadius = '9999px';
                        break;
                    case 'sharp':
                        visualCard.style.borderRadius = '0px';
                        break;
                    case 'hexagon':
                        visualCard.style.clipPath = 'polygon(25% 0%, 75% 0%, 100% 50%, 75% 100%, 25% 100%, 0% 50%)';
                        break;
                    default: // rectangle
                        const radius = parseInt(style.border_radius) || (isPanel ? 16 : 12);
                        visualCard.style.borderRadius = `${radius}px`;
                }
            }

            // ADAPTIVE CONTENT RESIZING
            if (window.adaptComponentContent) {
                window.adaptComponentContent(visualCard, shape, clampedW, clampedH);
            }

            const actualRect = visualCard.getBoundingClientRect();
            const actualW    = Math.round(actualRect.width);
            const actualH    = Math.round(actualRect.height);

            const display = document.getElementById('k-size-display-' + cId);
            if (display) display.textContent = `${actualW} × ${actualH} px (Req: ${reqWidth}×${reqHeight})`;

            const wSlider = document.getElementById('k-width-slider-'  + cId);
            const hSlider = document.getElementById('k-height-slider-' + cId);
            const wVal    = document.getElementById('k-wval-'          + cId);
            const hVal    = document.getElementById('k-hval-'          + cId);

            if (wSlider) wSlider.value = reqWidth;
            if (hSlider) hSlider.value = reqHeight;
            if (wVal)    wVal.textContent = `${reqWidth}px`;
            if (hVal)    hVal.textContent = `${reqHeight}px`;
        }

        function updateKitchenStyleDisplays() {
            Object.keys(window.kitchenCurrentStyles).forEach(cId => {
                const style = window.kitchenCurrentStyles[cId];
                if (!style) return;
                const isPanel  = cId.startsWith('k-list-');
                const defaultW = isPanel ? 320 : 240;
                const defaultH = isPanel ? 380 : 120;

                const w = style.width  || defaultW;
                const h = style.height || defaultH;

                const wSlider = document.getElementById('k-width-slider-'  + cId);
                const hSlider = document.getElementById('k-height-slider-' + cId);
                const wVal    = document.getElementById('k-wval-'          + cId);
                const hVal    = document.getElementById('k-hval-'          + cId);

                if (wSlider) wSlider.value = w;
                if (hSlider) hSlider.value = h;
                if (wVal)    wVal.textContent = `${w}px`;
                if (hVal)    hVal.textContent = `${h}px`;

                if (style.shape) setKitchenComponentShape(cId, style.shape);
            });
        }

        function saveKitchenLayout() {
            fetch('{{ route("workspace.save-layout") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ layout_matrix: {
                    kitchen_kpi_order:    window.kitchenKpiOrder,
                    kitchen_panels_order: window.kitchenPanelsOrder,
                    component_styles: { kitchen: JSON.parse(JSON.stringify(window.kitchenCurrentStyles)) }
                }})
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    window.kitchenLastSavedStyles = JSON.parse(JSON.stringify(window.kitchenCurrentStyles));
                    window.kitchenKpiLastSaved    = [...window.kitchenKpiOrder];
                    window.kitchenPanelsLastSaved = [...window.kitchenPanelsOrder];
                    showKitchenToast('✓ Urutan & tampilan dapur berhasil disimpan.', 'success');
                    toggleKitchenEditMode();
                } else {
                    showKitchenToast('Gagal menyimpan: ' + (data.message || 'Error'), 'error');
                }
            })
            .catch(() => showKitchenToast('Kesalahan koneksi saat menyimpan layout.', 'error'));
        }

        function cancelKitchenLayout() {
            window.kitchenCurrentStyles = JSON.parse(JSON.stringify(window.kitchenLastSavedStyles));
            window.kitchenKpiOrder      = [...window.kitchenKpiLastSaved];
            window.kitchenPanelsOrder   = [...window.kitchenPanelsLastSaved];

            // Reorder KPI cards in DOM
            const statsContainer = document.getElementById('kitchen-stats-container');
            if (statsContainer && window.kitchenKpiOrder.length) {
                const wrappers = {};
                statsContainer.querySelectorAll('[data-kpi-wrapper]').forEach(w => { wrappers[w.dataset.kpiWrapper] = w; });
                window.kitchenKpiOrder.forEach(kId => { if (wrappers[kId]) statsContainer.appendChild(wrappers[kId]); });
            }

            // Reorder Kanban panels in DOM
            const kanbanContainer = document.getElementById('kitchen-kanban-container');
            if (kanbanContainer && window.kitchenPanelsOrder.length) {
                const wrappers = {};
                kanbanContainer.querySelectorAll('[data-panel-wrapper]').forEach(w => { wrappers[w.dataset.panelWrapper] = w; });
                window.kitchenPanelsOrder.forEach(pId => { if (wrappers[pId]) kanbanContainer.appendChild(wrappers[pId]); });
            }

            Object.keys(window.kitchenCurrentStyles).forEach(cId => {
                applyKitchenComponentStyle(cId, window.kitchenCurrentStyles[cId]);
            });
            showKitchenToast('Perubahan dibatalkan.', 'info');
            toggleKitchenEditMode();
        }

        function resetKitchenLayout() {
            if (!confirm('Reset tampilan dapur ke default sistem?')) return;
            fetch('{{ route("workspace.reset-layout") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ page: 'kitchen' })
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    const defaultKpiOrder    = data.layout_matrix?.kitchen_kpi_order    || ['waiting', 'cooking', 'packing', 'done'];
                    const defaultPanelsOrder = data.layout_matrix?.kitchen_panels_order || ['k-list-waiting', 'k-list-cooking', 'k-list-packing', 'k-list-done'];
                    window.kitchenKpiOrder        = [...defaultKpiOrder];
                    window.kitchenKpiLastSaved    = [...defaultKpiOrder];
                    window.kitchenPanelsOrder     = [...defaultPanelsOrder];
                    window.kitchenPanelsLastSaved = [...defaultPanelsOrder];

                    const resetStyles = (data.layout_matrix?.component_styles || {}).kitchen || window.kitchenDefaultStyles;
                    window.kitchenCurrentStyles   = Object.assign({}, resetStyles);
                    window.kitchenLastSavedStyles = JSON.parse(JSON.stringify(window.kitchenCurrentStyles));

                    // Reorder KPI cards in DOM
                    const statsContainer = document.getElementById('kitchen-stats-container');
                    if (statsContainer && window.kitchenKpiOrder.length) {
                        const wrappers = {};
                        statsContainer.querySelectorAll('[data-kpi-wrapper]').forEach(w => { wrappers[w.dataset.kpiWrapper] = w; });
                        window.kitchenKpiOrder.forEach(kId => { if (wrappers[kId]) statsContainer.appendChild(wrappers[kId]); });
                    }

                    // Reorder Kanban panels in DOM
                    const kanbanContainer = document.getElementById('kitchen-kanban-container');
                    if (kanbanContainer && window.kitchenPanelsOrder.length) {
                        const wrappers = {};
                        kanbanContainer.querySelectorAll('[data-panel-wrapper]').forEach(w => { wrappers[w.dataset.panelWrapper] = w; });
                        window.kitchenPanelsOrder.forEach(pId => { if (wrappers[pId]) kanbanContainer.appendChild(wrappers[pId]); });
                    }

                    Object.keys(window.kitchenCurrentStyles).forEach(cId => {
                        applyKitchenComponentStyle(cId, window.kitchenCurrentStyles[cId]);
                    });
                    showKitchenToast('✓ Tampilan dapur dikembalikan ke default.', 'success');
                    if (window.kitchenIsEditMode) toggleKitchenEditMode();
                }
            })
            .catch(() => showKitchenToast('Gagal melakukan reset layout.', 'error'));
        }

        function resetSingleKitchenComponent(cId) {
            const isPanel  = cId.startsWith('k-list-');
            const defaults = (window.kitchenDefaultStyles && window.kitchenDefaultStyles[cId]) || (
                isPanel ? { shape: 'rectangle', width: 320, height: 380, border_radius: 16 } : { shape: 'rectangle', width: 240, height: 120, border_radius: 12 }
            );
            window.kitchenCurrentStyles[cId] = Object.assign({}, defaults);
            applyKitchenComponentStyle(cId, window.kitchenCurrentStyles[cId]);
            updateKitchenStyleDisplays();
            showKitchenToast('✓ Komponen dikembalikan ke default.', 'success');
        }

        function showKitchenToast(msg, type = 'success') {
            const toast = document.getElementById('kitchen-toast');
            const msgEl = document.getElementById('kitchen-toast-message');
            const icon  = document.getElementById('kitchen-toast-icon');
            if (!toast || !msgEl || !icon) return;
            msgEl.textContent = msg;
            icon.textContent  = type === 'success' ? '✓' : (type === 'error' ? '✕' : 'ℹ');
            toast.classList.remove('hidden', 'translate-y-4', 'opacity-0');
            setTimeout(() => {
                toast.classList.add('translate-y-4', 'opacity-0');
                setTimeout(() => toast.classList.add('hidden'), 300);
            }, 3000);
        }
    </script>
</x-dashboard-layout>
