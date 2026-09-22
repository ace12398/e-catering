<x-dashboard-layout>
    <x-slot name="title">Pengiriman & Kurir — E-Catering</x-slot>
    <x-slot name="toolbarTitle">Manajemen Pengiriman</x-slot>

    {{-- SortableJS untuk KPI Stats & Delivery Items Reorder --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

    @php
        $effectiveMatrix = $layoutMatrix ?? auth()->user()?->preference?->effective_layout_matrix ?? \App\Models\WorkspacePreference::getDefaultLayoutMatrix();
        $deliverySavedStyles = $deliveryComponentStyles ?? $effectiveMatrix['component_styles']['delivery'] ?? [];
        $defaultDeliveryStyles = \App\Models\WorkspacePreference::getDefaultLayoutMatrix()['component_styles']['delivery'];
        $deliveryKpiOrder = $deliveryKpiOrder ?? $effectiveMatrix['delivery_kpi_order'] ?? ['waiting', 'on_delivery', 'done', 'total'];
        $deliveryItemsOrder = $deliveryItemsOrder ?? $effectiveMatrix['delivery_items_order'] ?? [];

        $all = $deliveries->getCollection();

        // Master definition of delivery KPI stat cards
        $allStats = [
            'waiting'     => ['id'=>'waiting',    'label'=>'Menunggu',         'statuses'=>['waiting','siap_diambil'],      'color'=>'slate',   'icon'=>'⏳', 'useTotal'=>false],
            'on_delivery' => ['id'=>'on_delivery','label'=>'Dalam Perjalanan', 'statuses'=>['dalam_pengiriman','on_delivery'],'color'=>'amber', 'icon'=>'🛵', 'useTotal'=>false],
            'done'        => ['id'=>'done',       'label'=>'Selesai',          'statuses'=>['selesai','delivered'],          'color'=>'emerald', 'icon'=>'✅', 'useTotal'=>false],
            'total'       => ['id'=>'total',      'label'=>'Total Semua',      'statuses'=>[],                              'color'=>'blue',    'icon'=>'📦', 'useTotal'=>true],
        ];

        // Susun urutan KPI sesuai preference
        $sortedStats = [];
        foreach ($deliveryKpiOrder as $kId) {
            if (isset($allStats[$kId])) $sortedStats[] = $allStats[$kId];
        }
        foreach ($allStats as $kId => $data) {
            if (!in_array($kId, $deliveryKpiOrder)) $sortedStats[] = $data;
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

    <div class="max-w-6xl mx-auto space-y-6 relative" id="delivery-root">
        {{-- Flash Notification --}}
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold shadow-sm">✅ {{ session('success') }}</div>
        @endif

        {{-- TOAST NOTIFICATION --}}
        <div id="delivery-toast" class="hidden fixed bottom-6 right-6 z-50 p-4 rounded-xl bg-slate-900 text-white shadow-2xl border border-slate-700 flex items-center space-x-3 transition-all transform translate-y-4 opacity-0">
            <span id="delivery-toast-icon" class="text-amber-400 text-lg font-bold">✓</span>
            <span id="delivery-toast-message" class="text-xs font-medium">Layout pengiriman berhasil diperbarui.</span>
        </div>

        {{-- CANONICAL ADAPTIVE MODE BANNER (MATCHING /finance & /dashboard) --}}
        <div id="delivery-edit-bar" class="hidden sticky top-4 z-40 p-4 rounded-2xl bg-amber-950/90 dark:bg-amber-950/95 backdrop-blur border-2 border-amber-500 text-white shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center space-x-3">
                <span class="p-2.5 rounded-xl bg-amber-500/20 border border-amber-400/30 text-amber-300 font-mono text-base">✨</span>
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-300">Mode Penyesuaian Tampilan Aktif — Pengiriman & Kurir</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500 text-slate-950">Drag & Drop Aktif</span>
                    </div>
                    <p class="text-xs text-amber-100/90 mt-0.5">Ubah bentuk (shape), lebar, tinggi, dan geser kartu status serta kartu pesanan pengiriman secara live.</p>
                </div>
            </div>
            <div class="flex items-center space-x-2 shrink-0">
                <button type="button" onclick="cancelDeliveryLayout()" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs transition-colors border border-slate-600">✕ Batal</button>
                <button type="button" onclick="resetDeliveryLayout()" class="px-3.5 py-2 rounded-xl bg-amber-900/80 hover:bg-amber-800 text-amber-200 font-semibold text-xs transition-colors border border-amber-600">🔄 Reset Default</button>
                <button type="button" onclick="saveDeliveryLayout()" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold text-xs shadow-md transition-colors flex items-center space-x-1.5"><span>💾 Simpan Tampilan</span></button>
            </div>
        </div>

        {{-- Header Section --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Manajemen Pengiriman</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Pantau status pengiriman dan perbarui progres kurir.</p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Total: {{ $deliveries->total() }} Pengiriman
                </span>
                {{-- TOMBOL SESUAIKAN TAMPILAN PENGIRIMAN --}}
                <button type="button" id="delivery-btn-edit" onclick="toggleDeliveryEditMode()"
                        class="h-9 px-4 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs transition-colors flex items-center space-x-2 shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <span id="delivery-btn-text">⚙️ Sesuaikan Tampilan</span>
                </button>
            </div>
        </div>

        {{-- 1. TOP SUMMARY STATS CARDS (SORTABLE & RESIZABLE) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-start" id="delivery-stats-container">
            @foreach($sortedStats as $stat)
                @php $statId = $stat['id']; @endphp
                <div data-kpi-wrapper="{{ $statId }}" class="w-full flex flex-col items-center space-y-3 transition-all">
                    
                    {{-- BUSINESS PREVIEW CARD --}}
                    <div id="delivery-stat-card-{{ $statId }}" data-component-id="{{ $statId }}"
                         class="caterflow-visual-card w-full p-4 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm space-y-1 transition-all relative overflow-hidden flex flex-col justify-between">
                        
                        {{-- Dedicated Drag Handle Badge (Edit Mode Only) --}}
                        <div class="delivery-kpi-drag-handle hidden absolute top-2 right-2 z-10 px-2 py-0.5 rounded-md bg-amber-500/20 hover:bg-amber-500 text-amber-800 dark:text-amber-200 hover:text-white border border-amber-400/40 cursor-grab active:cursor-grabbing transition-colors flex items-center space-x-1 select-none shadow-sm"
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
                            @if($stat['useTotal'])
                                {{ $deliveries->total() }}
                            @else
                                {{ $all->whereIn('status', $stat['statuses'])->count() }}
                            @endif
                        </div>
                    </div>

                    {{-- SEPARATE RECTANGULAR CONFIGURATION EDITOR (OUTSIDE PREVIEW) --}}
                    <div class="delivery-edit-controls hidden w-full p-3.5 rounded-xl bg-slate-900/90 text-white border border-slate-700/60 shadow-lg space-y-2.5 text-xs backdrop-blur z-20">
                        <div class="flex items-center justify-between font-bold border-b border-slate-700 pb-1.5 text-[10px]">
                            <div class="flex items-center space-x-1.5">
                                <span class="delivery-kpi-drag-handle cursor-grab active:cursor-grabbing px-2 py-0.5 rounded bg-amber-600 hover:bg-amber-500 text-white text-[10px] font-bold flex items-center space-x-1 shadow-sm transition-colors select-none"
                                      title="Tahan dan geser untuk memindahkan urutan">
                                    <span>⠿</span>
                                    <span>Geser</span>
                                </span>
                                <span class="text-amber-400 font-bold">Atur: {{ $stat['label'] }}</span>
                            </div>
                            <button type="button" onclick="resetSingleDeliveryComponent('{{ $statId }}')" class="px-1.5 py-0.5 rounded text-[9px] font-semibold bg-amber-800/80 hover:bg-amber-700 text-white border border-amber-600 transition-colors" title="Reset komponen ini">🔄 Reset</button>
                        </div>
                        <div class="space-y-1">
                            <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Bentuk</span>
                            <div class="grid grid-cols-3 gap-1">
                                @foreach(['rectangle'=>'■ Kotak','rounded'=>'◉ Bulat','sharp'=>'▪ Tajam','pill'=>'⬭ Pil','circle'=>'○ Lingkar','hexagon'=>'⬡ Hex'] as $sId => $sLabel)
                                    <button type="button" onclick="setDeliveryComponentShape('{{ $statId }}', '{{ $sId }}')" class="d-shape-btn-{{ $statId }} px-1 py-1 rounded text-[9px] font-semibold border text-center transition-colors bg-white text-slate-800 border-slate-300 hover:bg-amber-500 hover:text-white" data-shape="{{ $sId }}">{{ $sLabel }}</button>
                                @endforeach
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-1.5">
                            <div>
                                <div class="flex items-center justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                                    <span>Lebar</span>
                                    <span id="d-wval-{{ $statId }}" class="text-amber-400 font-mono font-bold">240px</span>
                                </div>
                                <input type="range" min="140" max="400" step="10" value="240" id="d-width-slider-{{ $statId }}" oninput="onDeliverySliderInput('{{ $statId }}', 'width', this.value)" class="w-full accent-amber-500">
                            </div>
                            <div>
                                <div class="flex items-center justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                                    <span>Tinggi</span>
                                    <span id="d-hval-{{ $statId }}" class="text-amber-400 font-mono font-bold">120px</span>
                                </div>
                                <input type="range" min="70" max="240" step="10" value="120" id="d-height-slider-{{ $statId }}" oninput="onDeliverySliderInput('{{ $statId }}', 'height', this.value)" class="w-full accent-amber-500">
                            </div>
                        </div>
                        <div class="text-[9px] font-mono text-amber-400 pt-1 border-t border-slate-700/60" id="d-size-display-{{ $statId }}">240 × 120 px</div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- 2. DELIVERY ITEMS LIST (SORTABLE & RESIZABLE) --}}
        <div class="space-y-4" id="delivery-items-container">
            @forelse($deliveries as $delivery)
                @php
                    $delCardId = 'delivery_order_' . $delivery->id;
                    $statusMap = [
                        'waiting'          => ['label'=>'Menunggu','bg'=>'bg-slate-100 dark:bg-slate-800','text'=>'text-slate-600 dark:text-slate-300','icon'=>'⏳'],
                        'siap_diambil'     => ['label'=>'Siap Diambil','bg'=>'bg-blue-100 dark:bg-blue-950/40','text'=>'text-blue-700 dark:text-blue-300','icon'=>'📦'],
                        'dalam_pengiriman' => ['label'=>'Dalam Perjalanan','bg'=>'bg-amber-100 dark:bg-amber-950/40','text'=>'text-amber-700 dark:text-amber-300','icon'=>'🛵'],
                        'on_delivery'      => ['label'=>'Dalam Perjalanan','bg'=>'bg-amber-100 dark:bg-amber-950/40','text'=>'text-amber-700 dark:text-amber-300','icon'=>'🛵'],
                        'selesai'          => ['label'=>'Selesai','bg'=>'bg-emerald-100 dark:bg-emerald-950/40','text'=>'text-emerald-700 dark:text-emerald-300','icon'=>'✅'],
                        'delivered'        => ['label'=>'Selesai','bg'=>'bg-emerald-100 dark:bg-emerald-950/40','text'=>'text-emerald-700 dark:text-emerald-300','icon'=>'✅'],
                        'assigned'         => ['label'=>'Ditugaskan','bg'=>'bg-indigo-100 dark:bg-indigo-950/40','text'=>'text-indigo-700 dark:text-indigo-300','icon'=>'👤'],
                    ];
                    $sm = $statusMap[$delivery->status] ?? ['label'=>$delivery->status,'bg'=>'bg-slate-100','text'=>'text-slate-700','icon'=>'📋'];
                    $canPickup = in_array($delivery->status, ['waiting','siap_diambil','assigned']);
                    $canComplete = in_array($delivery->status, ['dalam_pengiriman','on_delivery']);
                @endphp
                <div data-item-wrapper="{{ $delivery->id }}" class="w-full flex flex-col items-center space-y-3 transition-all">
                    
                    {{-- BUSINESS PREVIEW CARD FOR DELIVERY ORDER --}}
                    <div id="delivery-stat-card-{{ $delCardId }}" data-component-id="{{ $delCardId }}"
                         class="caterflow-visual-card p-5 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm transition-all duration-300 w-full overflow-hidden relative">
                        
                        {{-- Dedicated Drag Handle Badge (Edit Mode Only) --}}
                        <div class="delivery-item-drag-handle hidden absolute top-2 right-2 z-10 px-2 py-0.5 rounded-md bg-amber-500/20 hover:bg-amber-500 text-amber-800 dark:text-amber-200 hover:text-white border border-amber-400/40 cursor-grab active:cursor-grabbing transition-colors flex items-center space-x-1 select-none shadow-sm"
                             title="Tahan dan geser untuk memindahkan urutan kartu pesanan">
                            <svg class="w-3 h-3 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 8h16M4 16h16"></path>
                            </svg>
                            <span class="text-[9px] font-extrabold font-mono pointer-events-none">GESER</span>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 flex-between-header">
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 rounded-xl {{ $sm['bg'] }} flex items-center justify-center text-xl adaptive-icon shrink-0">{{ $sm['icon'] }}</div>
                                <div class="space-y-0.5">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm font-bold text-slate-900 dark:text-white stat-value truncate">
                                            {{ $delivery->order?->order_number ?? 'DEL-' . $delivery->id }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase {{ $sm['bg'] }} {{ $sm['text'] }} border border-current badge-adaptive shrink-0">
                                            {{ $sm['label'] }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-500 subtext">
                                        Kurir: {{ $delivery->courier?->name ?? 'Belum Ditugaskan' }}
                                        @if($delivery->recipient_name) • Penerima: {{ $delivery->recipient_name }} @endif
                                    </p>
                                    <p class="text-[11px] text-slate-400 subtext">
                                        🚗 {{ $delivery->vehicle ?? 'Motor' }}
                                        @if($delivery->order?->delivery_address) • {{ Str::limit($delivery->order->delivery_address, 40) }} @endif
                                    </p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <div class="text-[11px] text-slate-400 text-right secondary-info">
                                    @if($delivery->pickup_time) Pickup: {{ $delivery->pickup_time->format('d M, H:i') }}<br> @endif
                                    @if($delivery->delivered_time) Tiba: {{ $delivery->delivered_time->format('d M, H:i') }} @endif
                                </div>
                                <div class="flex gap-2">
                                    @if($canPickup)
                                    <form method="POST" action="{{ route('delivery.update-status', $delivery->id) }}" onsubmit="return confirm('Konfirmasi ambil pesanan ini?')">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="action" value="pickup">
                                        <button type="submit" class="px-3.5 py-1.5 rounded-xl text-[11px] font-bold bg-amber-500 text-white hover:bg-amber-600 transition-colors btn-adaptive shadow-sm">
                                            🛵 Ambil Pesanan
                                        </button>
                                    </form>
                                    @elseif($canComplete)
                                    <form method="POST" action="{{ route('delivery.update-status', $delivery->id) }}" onsubmit="return confirm('Konfirmasi pesanan sudah diterima?')">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="action" value="complete">
                                        <button type="submit" class="px-3.5 py-1.5 rounded-xl text-[11px] font-bold bg-emerald-500 text-white hover:bg-emerald-600 transition-colors btn-adaptive shadow-sm">
                                            ✅ Selesai
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SEPARATE RECTANGULAR CONFIGURATION EDITOR (OUTSIDE PREVIEW) --}}
                    <div class="delivery-edit-controls hidden w-full p-3.5 rounded-xl bg-slate-900/90 text-white border border-slate-700/60 shadow-lg space-y-2.5 text-xs backdrop-blur z-20">
                        <div class="flex items-center justify-between font-bold border-b border-slate-700 pb-1.5 text-[10px]">
                            <div class="flex items-center space-x-1.5">
                                <span class="delivery-item-drag-handle cursor-grab active:cursor-grabbing px-2 py-0.5 rounded bg-amber-600 hover:bg-amber-500 text-white text-[10px] font-bold flex items-center space-x-1 shadow-sm transition-colors select-none"
                                      title="Tahan dan geser untuk memindahkan urutan">
                                    <span>⠿</span>
                                    <span>Geser</span>
                                </span>
                                <span class="text-amber-400 font-bold">Atur: {{ $delivery->order?->order_number ?? 'DEL-' . $delivery->id }}</span>
                            </div>
                            <button type="button" onclick="resetSingleDeliveryComponent('{{ $delCardId }}')" class="px-1.5 py-0.5 rounded text-[9px] font-semibold bg-amber-800/80 hover:bg-amber-700 text-white border border-amber-600 transition-colors" title="Reset komponen ini">🔄 Reset</button>
                        </div>
                        <div class="space-y-1">
                            <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Bentuk</span>
                            <div class="grid grid-cols-3 gap-1">
                                @foreach(['rectangle'=>'■ Kotak','rounded'=>'◉ Bulat','sharp'=>'▪ Tajam','pill'=>'⬭ Pil','circle'=>'○ Lingkar','hexagon'=>'⬡ Hex'] as $sId => $sLabel)
                                    <button type="button" onclick="setDeliveryComponentShape('{{ $delCardId }}', '{{ $sId }}')" class="d-shape-btn-{{ $delCardId }} px-1 py-1 rounded text-[9px] font-semibold border text-center transition-colors bg-white text-slate-800 border-slate-300 hover:bg-amber-500 hover:text-white" data-shape="{{ $sId }}">{{ $sLabel }}</button>
                                @endforeach
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-1.5">
                            <div>
                                <div class="flex items-center justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                                    <span>Lebar</span>
                                    <span id="d-wval-{{ $delCardId }}" class="text-amber-400 font-mono font-bold">320px</span>
                                </div>
                                <input type="range" min="180" max="600" step="10" value="320" id="d-width-slider-{{ $delCardId }}" oninput="onDeliverySliderInput('{{ $delCardId }}', 'width', this.value)" class="w-full accent-amber-500">
                            </div>
                            <div>
                                <div class="flex items-center justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                                    <span>Tinggi</span>
                                    <span id="d-hval-{{ $delCardId }}" class="text-amber-400 font-mono font-bold">160px</span>
                                </div>
                                <input type="range" min="100" max="400" step="10" value="160" id="d-height-slider-{{ $delCardId }}" oninput="onDeliverySliderInput('{{ $delCardId }}', 'height', this.value)" class="w-full accent-amber-500">
                            </div>
                        </div>
                        <div class="text-[9px] font-mono text-amber-400 pt-1 border-t border-slate-700/60" id="d-size-display-{{ $delCardId }}">320 × 160 px</div>
                    </div>
                </div>
            @empty
                <div class="p-16 text-center rounded-2xl bg-white dark:bg-slate-850 border border-slate-200">
                    <div class="text-4xl mb-3">🛵</div>
                    <p class="text-slate-500 text-sm font-medium">Belum ada data pengiriman.</p>
                </div>
            @endforelse

            <div class="pt-2">{{ $deliveries->links() }}</div>
        </div>
    </div>

    {{-- DELIVERY CUSTOMIZATION ENGINE (CANONICAL MASTER /finance) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.deliveryIsEditMode       = false;
            window.deliveryKpiOrder         = @json($deliveryKpiOrder);
            window.deliveryKpiLastSaved     = [...window.deliveryKpiOrder];
            window.deliveryItemsOrder       = @json($deliveryItemsOrder);
            window.deliveryItemsLastSaved   = [...window.deliveryItemsOrder];
            window.deliverySavedStyles      = @json($deliverySavedStyles);
            window.deliveryDefaultStyles    = @json($defaultDeliveryStyles);
            window.deliveryCurrentStyles    = Object.assign({}, window.deliveryDefaultStyles, window.deliverySavedStyles);
            window.deliveryLastSavedStyles  = JSON.parse(JSON.stringify(window.deliveryCurrentStyles));
            window.deliveryKpiSortable      = null;
            window.deliveryItemsSortable    = null;

            initDeliverySortables();
            initDeliveryComponentStyles();
        });

        function initDeliverySortables() {
            // 1. Sortable for Top KPI Stats Cards
            const statsContainer = document.getElementById('delivery-stats-container');
            if (statsContainer) {
                window.deliveryKpiSortable = new Sortable(statsContainer, {
                    animation: 200,
                    handle: '.delivery-kpi-drag-handle',
                    ghostClass: 'dashboard-sortable-ghost',
                    dragClass: 'dashboard-sortable-drag',
                    chosenClass: 'dashboard-sortable-chosen',
                    disabled: true,
                    onEnd: function() {
                        const newOrder = [];
                        statsContainer.querySelectorAll('[data-kpi-wrapper]').forEach(el => newOrder.push(el.dataset.kpiWrapper));
                        window.deliveryKpiOrder = newOrder;
                    }
                });
            }

            // 2. Sortable for Delivery Order Items Cards
            const itemsContainer = document.getElementById('delivery-items-container');
            if (itemsContainer) {
                window.deliveryItemsSortable = new Sortable(itemsContainer, {
                    animation: 200,
                    handle: '.delivery-item-drag-handle',
                    ghostClass: 'dashboard-sortable-ghost',
                    dragClass: 'dashboard-sortable-drag',
                    chosenClass: 'dashboard-sortable-chosen',
                    disabled: true,
                    onEnd: function() {
                        const newOrder = [];
                        itemsContainer.querySelectorAll('[data-item-wrapper]').forEach(el => newOrder.push(parseInt(el.dataset.itemWrapper)));
                        window.deliveryItemsOrder = newOrder;
                    }
                });
            }
        }

        function initDeliveryComponentStyles() {
            const allDeliveryCardEls = document.querySelectorAll('[data-component-id]');
            const allDeliveryIds = Array.from(allDeliveryCardEls).map(el => el.dataset.componentId);

            allDeliveryIds.forEach(id => {
                const style = window.deliveryCurrentStyles[id];
                if (style) applyDeliveryComponentStyle(id, style);
            });
        }

        function toggleDeliveryEditMode() {
            window.deliveryIsEditMode = !window.deliveryIsEditMode;
            const bar  = document.getElementById('delivery-edit-bar');
            const btn  = document.getElementById('delivery-btn-text');
            const root = document.getElementById('delivery-root');

            if (window.deliveryIsEditMode) {
                bar.classList.remove('hidden');
                btn.textContent = '✕ Keluar Mode Penyesuaian';
                root.classList.add('in-edit-mode');
                document.querySelectorAll('.delivery-edit-controls').forEach(el => el.classList.remove('hidden'));
                document.querySelectorAll('.delivery-kpi-drag-handle').forEach(el => el.classList.remove('hidden'));
                document.querySelectorAll('.delivery-item-drag-handle').forEach(el => el.classList.remove('hidden'));
                document.querySelectorAll('[data-kpi-wrapper], [data-item-wrapper]').forEach(el => {
                    el.classList.add('p-1.5', 'rounded-2xl', 'border-2', 'border-dashed', 'border-amber-500/50', 'bg-amber-50/10');
                });
            } else {
                bar.classList.add('hidden');
                btn.textContent = '⚙️ Sesuaikan Tampilan';
                root.classList.remove('in-edit-mode');
                document.querySelectorAll('.delivery-edit-controls').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('.delivery-kpi-drag-handle').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('.delivery-item-drag-handle').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('[data-kpi-wrapper], [data-item-wrapper]').forEach(el => {
                    el.classList.remove('p-1.5', 'rounded-2xl', 'border-2', 'border-dashed', 'border-amber-500/50', 'bg-amber-50/10');
                });
            }

            if (window.deliveryKpiSortable) window.deliveryKpiSortable.option('disabled', !window.deliveryIsEditMode);
            if (window.deliveryItemsSortable) window.deliveryItemsSortable.option('disabled', !window.deliveryIsEditMode);
            if (window.deliveryIsEditMode) updateDeliveryStyleDisplays();
        }

        function setDeliveryComponentShape(cId, shape) {
            if (!window.deliveryCurrentStyles[cId]) window.deliveryCurrentStyles[cId] = {};
            window.deliveryCurrentStyles[cId].shape = shape;
            applyDeliveryComponentStyle(cId, window.deliveryCurrentStyles[cId]);

            document.querySelectorAll('.d-shape-btn-' + cId).forEach(btn => {
                const isActive = btn.dataset.shape === shape;
                btn.classList.toggle('bg-amber-500',   isActive);
                btn.classList.toggle('text-white',       isActive);
                btn.classList.toggle('border-amber-600', isActive);
                btn.classList.toggle('bg-white',        !isActive);
                btn.classList.toggle('text-slate-800',  !isActive);
                btn.classList.toggle('border-slate-300',!isActive);
            });
        }

        function onDeliverySliderInput(cId, dim, value) {
            const v = parseInt(value);
            if (isNaN(v)) return;
            if (!window.deliveryCurrentStyles[cId]) window.deliveryCurrentStyles[cId] = {};
            window.deliveryCurrentStyles[cId][dim] = v;
            applyDeliveryComponentStyle(cId, window.deliveryCurrentStyles[cId]);
        }

        function applyDeliveryComponentStyle(cId, style) {
            if (!style) return;
            const visualCard = document.querySelector('[data-component-id="' + cId + '"]');
            if (!visualCard) return;

            const isOrderCard = cId.startsWith('delivery_order_');
            const defaultW    = isOrderCard ? 320 : 240;
            const defaultH    = isOrderCard ? 160 : 120;
            const reqWidth    = parseInt(style.width)  || defaultW;
            const reqHeight   = parseInt(style.height) || defaultH;
            const shape       = style.shape || 'rectangle';

            const bounds = isOrderCard
                ? { minW: 180, maxW: 600, minH: 100, maxH: 400 }
                : { minW: 140, maxW: 400, minH: 70,  maxH: 240 };

            const clampedW = Math.max(bounds.minW, Math.min(bounds.maxW, reqWidth));
            const clampedH = Math.max(bounds.minH, Math.min(bounds.maxH, reqHeight));

            visualCard.style.maxWidth     = '';
            visualCard.style.maxHeight    = '';
            visualCard.style.minWidth     = '';
            visualCard.style.minHeight    = '';
            visualCard.style.boxSizing    = 'border-box';
            visualCard.style.clipPath     = '';
            visualCard.style.aspectRatio  = '';
            visualCard.style.borderRadius = '';
            visualCard.style.alignSelf    = '';

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
                        const radius = parseInt(style.border_radius) || 16;
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

            const display = document.getElementById('d-size-display-' + cId);
            if (display) display.textContent = `${actualW} × ${actualH} px (Req: ${reqWidth}×${reqHeight})`;

            const wSlider = document.getElementById('d-width-slider-'  + cId);
            const hSlider = document.getElementById('d-height-slider-' + cId);
            const wVal    = document.getElementById('d-wval-'          + cId);
            const hVal    = document.getElementById('d-hval-'          + cId);

            if (wSlider) wSlider.value = reqWidth;
            if (hSlider) hSlider.value = reqHeight;
            if (wVal)    wVal.textContent = `${reqWidth}px`;
            if (hVal)    hVal.textContent = `${reqHeight}px`;
        }

        function updateDeliveryStyleDisplays() {
            const allDeliveryCardEls = document.querySelectorAll('[data-component-id]');
            const allDeliveryIds = Array.from(allDeliveryCardEls).map(el => el.dataset.componentId);

            allDeliveryIds.forEach(cId => {
                const style = window.deliveryCurrentStyles[cId];
                if (!style) return;
                const isOrderCard = cId.startsWith('delivery_order_');
                const defaultW    = isOrderCard ? 320 : 240;
                const defaultH    = isOrderCard ? 160 : 120;

                const w = style.width  || defaultW;
                const h = style.height || defaultH;

                const wSlider = document.getElementById('d-width-slider-'  + cId);
                const hSlider = document.getElementById('d-height-slider-' + cId);
                const wVal    = document.getElementById('d-wval-'          + cId);
                const hVal    = document.getElementById('d-hval-'          + cId);

                if (wSlider) wSlider.value = w;
                if (hSlider) hSlider.value = h;
                if (wVal)    wVal.textContent = `${w}px`;
                if (hVal)    hVal.textContent = `${h}px`;

                if (style.shape) setDeliveryComponentShape(cId, style.shape);
            });
        }

        function saveDeliveryLayout() {
            fetch('{{ route("workspace.save-layout") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ layout_matrix: {
                    delivery_kpi_order:   window.deliveryKpiOrder,
                    delivery_items_order: window.deliveryItemsOrder,
                    component_styles: { delivery: JSON.parse(JSON.stringify(window.deliveryCurrentStyles)) }
                }})
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    window.deliveryLastSavedStyles = JSON.parse(JSON.stringify(window.deliveryCurrentStyles));
                    window.deliveryKpiLastSaved    = [...window.deliveryKpiOrder];
                    window.deliveryItemsLastSaved  = [...window.deliveryItemsOrder];
                    showDeliveryToast('✓ Urutan & tampilan pengiriman berhasil disimpan.', 'success');
                    toggleDeliveryEditMode();
                } else {
                    showDeliveryToast('Gagal menyimpan: ' + (data.message || 'Error'), 'error');
                }
            })
            .catch(() => showDeliveryToast('Kesalahan koneksi saat menyimpan layout.', 'error'));
        }

        function cancelDeliveryLayout() {
            window.deliveryCurrentStyles = JSON.parse(JSON.stringify(window.deliveryLastSavedStyles));
            window.deliveryKpiOrder      = [...window.deliveryKpiLastSaved];
            window.deliveryItemsOrder    = [...window.deliveryItemsLastSaved];

            // Reorder KPI cards in DOM
            const statsContainer = document.getElementById('delivery-stats-container');
            if (statsContainer && window.deliveryKpiOrder.length) {
                const wrappers = {};
                statsContainer.querySelectorAll('[data-kpi-wrapper]').forEach(w => { wrappers[w.dataset.kpiWrapper] = w; });
                window.deliveryKpiOrder.forEach(kId => { if (wrappers[kId]) statsContainer.appendChild(wrappers[kId]); });
            }

            // Reorder Delivery item cards in DOM
            const itemsContainer = document.getElementById('delivery-items-container');
            if (itemsContainer && window.deliveryItemsOrder.length) {
                const wrappers = {};
                itemsContainer.querySelectorAll('[data-item-wrapper]').forEach(w => { wrappers[parseInt(w.dataset.itemWrapper)] = w; });
                window.deliveryItemsOrder.forEach(itemId => { if (wrappers[itemId]) itemsContainer.appendChild(wrappers[itemId]); });
            }

            Object.keys(window.deliveryCurrentStyles).forEach(cId => {
                applyDeliveryComponentStyle(cId, window.deliveryCurrentStyles[cId]);
            });
            showDeliveryToast('Perubahan dibatalkan.', 'info');
            toggleDeliveryEditMode();
        }

        function resetDeliveryLayout() {
            if (!confirm('Reset tampilan pengiriman ke default sistem?')) return;
            fetch('{{ route("workspace.reset-layout") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ page: 'delivery' })
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    const defaultKpiOrder   = data.layout_matrix?.delivery_kpi_order   || ['waiting', 'on_delivery', 'done', 'total'];
                    const defaultItemsOrder = data.layout_matrix?.delivery_items_order || [];
                    window.deliveryKpiOrder       = [...defaultKpiOrder];
                    window.deliveryKpiLastSaved   = [...defaultKpiOrder];
                    window.deliveryItemsOrder     = [...defaultItemsOrder];
                    window.deliveryItemsLastSaved = [...defaultItemsOrder];

                    const resetStyles = (data.layout_matrix?.component_styles || {}).delivery || window.deliveryDefaultStyles;
                    window.deliveryCurrentStyles   = Object.assign({}, resetStyles);
                    window.deliveryLastSavedStyles = JSON.parse(JSON.stringify(window.deliveryCurrentStyles));

                    // Reorder KPI cards in DOM
                    const statsContainer = document.getElementById('delivery-stats-container');
                    if (statsContainer && window.deliveryKpiOrder.length) {
                        const wrappers = {};
                        statsContainer.querySelectorAll('[data-kpi-wrapper]').forEach(w => { wrappers[w.dataset.kpiWrapper] = w; });
                        window.deliveryKpiOrder.forEach(kId => { if (wrappers[kId]) statsContainer.appendChild(wrappers[kId]); });
                    }

                    Object.keys(window.deliveryCurrentStyles).forEach(cId => {
                        applyDeliveryComponentStyle(cId, window.deliveryCurrentStyles[cId]);
                    });
                    showDeliveryToast('✓ Tampilan pengiriman dikembalikan ke default.', 'success');
                    if (window.deliveryIsEditMode) toggleDeliveryEditMode();
                }
            })
            .catch(() => showDeliveryToast('Gagal melakukan reset layout.', 'error'));
        }

        function resetSingleDeliveryComponent(cId) {
            const isOrderCard = cId.startsWith('delivery_order_');
            const defaults    = (window.deliveryDefaultStyles && window.deliveryDefaultStyles[cId]) || (
                isOrderCard ? { shape: 'rectangle', width: 320, height: 160, border_radius: 16 } : { shape: 'rectangle', width: 240, height: 120, border_radius: 12 }
            );
            window.deliveryCurrentStyles[cId] = Object.assign({}, defaults);
            applyDeliveryComponentStyle(cId, window.deliveryCurrentStyles[cId]);
            updateDeliveryStyleDisplays();
            showDeliveryToast('✓ Komponen dikembalikan ke default.', 'success');
        }

        function showDeliveryToast(msg, type = 'success') {
            const toast = document.getElementById('delivery-toast');
            const msgEl = document.getElementById('delivery-toast-message');
            const icon  = document.getElementById('delivery-toast-icon');
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
