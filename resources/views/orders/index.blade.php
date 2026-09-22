<x-dashboard-layout>
    <x-slot name="title">{{ auth()->user()->isAdmin() ? 'Semua Pesanan' : 'Riwayat & Pelacakan Pesanan' }} — E-Catering</x-slot>
    <x-slot name="toolbarTitle">{{ auth()->user()->isAdmin() ? 'Semua Pesanan' : 'Riwayat & Pelacakan' }}</x-slot>

    {{-- SortableJS CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

    @php
        $effectiveMatrix = $layoutMatrix ?? $layout_matrix ?? auth()->user()?->preference?->effective_layout_matrix ?? \App\Models\WorkspacePreference::getDefaultLayoutMatrix();
        $ordersSavedStyles = $ordersStyles ?? $effectiveMatrix['component_styles']['orders'] ?? [];
        $defaultOrdersStyles = \App\Models\WorkspacePreference::getDefaultLayoutMatrix()['component_styles']['orders'];
        $ordersSectionsOrder = $ordersSectionsOrder ?? $effectiveMatrix['orders_sections_order'] ?? ['O-HEADER', 'O-KPI', 'O-FILTER', 'O-LIST', 'O-PAGINATION'];
        $ordersKpiOrder = $ordersKpiOrder ?? $effectiveMatrix['orders_kpi_order'] ?? ['pending', 'preparing', 'on_delivery', 'completed'];

        // Stats calculation
        $allOrdersList  = $orders->items();
        $pendingCount   = count(array_filter($allOrdersList, fn($o) => in_array(is_object($o->status)?$o->status->value:(string)$o->status, ['pending','menunggu_pembayaran','menunggu_verifikasi'])));
        $preparingCount = count(array_filter($allOrdersList, fn($o) => in_array(is_object($o->status)?$o->status->value:(string)$o->status, ['preparing','processing','sedang_diproses','sedang_dimasak'])));
        $deliveryCount  = count(array_filter($allOrdersList, fn($o) => in_array(is_object($o->status)?$o->status->value:(string)$o->status, ['on_delivery','sedang_dikirim'])));
        $completedCount = count(array_filter($allOrdersList, fn($o) => in_array(is_object($o->status)?$o->status->value:(string)$o->status, ['completed','selesai'])));

        $allKpiData = [
            'pending' => ['id' => 'pending', 'label' => 'Menunggu Pembayaran', 'icon' => '⏳', 'value' => $pendingCount, 'subtext' => 'Perlu Verifikasi', 'color' => 'amber'],
            'preparing' => ['id' => 'preparing', 'label' => 'Diproses Dapur', 'icon' => '🍳', 'value' => $preparingCount, 'subtext' => 'Tahap Memasak', 'color' => 'blue'],
            'on_delivery' => ['id' => 'on_delivery', 'label' => 'Dikirim Kurir', 'icon' => '🚚', 'value' => $deliveryCount, 'subtext' => 'Dalam Perjalanan', 'color' => 'purple'],
            'completed' => ['id' => 'completed', 'label' => 'Pesanan Selesai', 'icon' => '✅', 'value' => $completedCount, 'subtext' => 'Transaksi Tuntas', 'color' => 'emerald'],
        ];

        $sortedKpiList = [];
        foreach ($ordersKpiOrder as $kId) if (isset($allKpiData[$kId])) $sortedKpiList[] = $allKpiData[$kId];
        foreach ($allKpiData as $kId => $kData) if (!in_array($kId, $ordersKpiOrder)) $sortedKpiList[] = $kData;
    @endphp

    <style>
        .dashboard-sortable-ghost {
            opacity: 0.35 !important;
            background: rgba(245, 158, 11, 0.12) !important;
            border: 2px dashed #f59e0b !important;
            border-radius: 1.25rem !important;
            box-shadow: inset 0 0 16px rgba(245, 158, 11, 0.25) !important;
        }
        .dashboard-sortable-drag {
            opacity: 0.95 !important;
            transform: scale(1.02) rotate(1deg) !important;
            box-shadow: 0 25px 30px -5px rgba(0, 0, 0, 0.25), 0 15px 15px -5px rgba(0, 0, 0, 0.15) !important;
            z-index: 9999 !important;
        }
        .dashboard-sortable-chosen {
            cursor: grabbing !important;
        }
        .orders-section-drag-handle, .orders-kpi-drag-handle {
            user-select: none;
            touch-action: none;
        }
    </style>

    <div class="max-w-6xl mx-auto space-y-6 relative" id="caterflow-orders-root">

        <div id="orders-toast" class="hidden fixed bottom-6 right-6 z-50 p-4 rounded-xl bg-slate-900 text-white shadow-2xl border border-slate-700 flex items-center space-x-3 transition-all transform translate-y-4 opacity-0">
            <span id="orders-toast-icon" class="text-amber-400 text-lg font-bold">✓</span>
            <span id="orders-toast-message" class="text-xs font-medium">Layout pesanan berhasil diperbarui.</span>
        </div>

        <div id="orders-edit-mode-bar" class="hidden sticky top-4 z-40 p-4 rounded-2xl bg-amber-950/90 dark:bg-amber-950/95 backdrop-blur border-2 border-amber-500 text-white shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center space-x-3">
                <span class="p-2.5 rounded-xl bg-amber-500/20 border border-amber-400/30 text-amber-300 font-mono text-base">✨</span>
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-300">Mode Penyesuaian Tampilan Aktif — {{ auth()->user()->isAdmin() ? 'Semua Pesanan' : 'Riwayat & Pelacakan' }}</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500 text-slate-950">Drag & Drop Aktif</span>
                    </div>
                    <p class="text-xs text-amber-100/90 mt-0.5">Ubah bentuk (shape), ukuran (lebar/tinggi), dan geser urutan bagian pesanan secara live.</p>
                </div>
            </div>
            <div class="flex items-center space-x-2 shrink-0">
                <button type="button" onclick="cancelOrdersLayout()" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs transition-colors border border-slate-600">✕ Batal</button>
                <button type="button" onclick="resetOrdersLayout()" class="px-3.5 py-2 rounded-xl bg-amber-900/80 hover:bg-amber-800 text-amber-200 font-semibold text-xs transition-colors border border-amber-600">🔄 Reset Default</button>
                <button type="button" onclick="saveOrdersLayout()" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold text-xs shadow-md transition-colors flex items-center space-x-1.5"><span>💾 Simpan Tampilan</span></button>
            </div>
        </div>

        <div id="orders-sections-container" class="space-y-6">
            @foreach($ordersSectionsOrder as $sectionId)
                @if($sectionId === 'O-HEADER')
                    <div data-orders-section="O-HEADER" data-widget-wrapper="O-HEADER" class="orders-component-item w-full flex flex-col space-y-3 transition-all relative">
                        <div class="orders-section-drag-handle hidden self-start px-2.5 py-1 rounded-lg bg-amber-500/20 hover:bg-amber-500 text-amber-800 dark:text-amber-200 hover:text-white border border-amber-400/40 cursor-grab active:cursor-grabbing font-mono text-xs font-bold transition-colors select-none shadow-sm mb-1">⠿ GESER BAGIAN: Header Riwayat</div>
                        <div id="orders-card-O-HEADER" data-component-id="O-HEADER" class="caterflow-visual-card w-full p-6 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4 overflow-hidden transition-all">
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-wider text-amber-600 dark:text-amber-400 stat-value">Order Management Workspace</span>
                                <h1 class="text-xl md:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight mt-0.5">{{ auth()->user()->isAdmin() ? 'Daftar Seluruh Pesanan' : 'Riwayat & Pelacakan Pesanan' }}</h1>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 subtext">{{ auth()->user()->isAdmin() ? 'Memantau seluruh transaksi pemesanan katering masuk dari semua pelanggan.' : 'Pantau status langsung dan riwayat transaksi katering Anda.' }}</p>
                            </div>
                            <div class="flex items-center space-x-2 shrink-0">
                                <span class="px-3.5 py-1.5 rounded-full text-xs font-extrabold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">Total: {{ $orders->total() }} Pesanan</span>
                                <button type="button" id="orders-btn-edit" onclick="toggleOrdersEditMode()" class="h-9 px-4 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs transition-colors flex items-center space-x-2 shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    <span id="orders-btn-text">⚙️ Sesuaikan Tampilan</span>
                                </button>
                            </div>
                        </div>
                        <div class="orders-edit-controls hidden w-full p-4 rounded-xl bg-slate-900/90 text-white border border-slate-700/60 shadow-lg space-y-3 text-xs backdrop-blur z-20">
                            <div class="flex items-center justify-between font-bold border-b border-slate-700 pb-2 text-xs">
                                <span class="text-amber-400 font-bold">Atur Komponen: Header Pesanan (O-HEADER)</span>
                                <button type="button" onclick="resetSingleOrdersComponent('O-HEADER')" class="px-2 py-1 rounded text-xs font-semibold bg-amber-800/80 hover:bg-amber-700 text-white border border-amber-600 transition-colors" title="Reset komponen ini">🔄 Reset</button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1.5">Bentuk</span>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach(['rectangle'=>'■ Kotak','rounded'=>'◉ Bulat','sharp'=>'▪ Tajam','pill'=>'⬭ Pil','circle'=>'○ Lingkar','hexagon'=>'⬡ Hex'] as $sId => $sLabel)
                                            <button type="button" onclick="setOrdersComponentShape('O-HEADER', '{{ $sId }}')" class="o-shape-btn-O-HEADER px-2.5 py-1.5 rounded-lg text-xs font-semibold border bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-600 text-slate-800 dark:text-slate-200 hover:bg-amber-500 hover:text-white transition-colors" data-shape="{{ $sId }}">{{ $sLabel }}</button>
                                        @endforeach
                                    </div>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5"><span>Lebar</span><span id="o-wval-O-HEADER" class="text-amber-400 font-mono font-bold">900px</span></div>
                                    <input type="range" min="240" max="900" step="10" value="900" id="o-width-slider-O-HEADER" oninput="onOrdersSliderInput('O-HEADER', 'width', this.value)" class="w-full accent-amber-500">
                                </div>
                                <div>
                                    <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5"><span>Tinggi</span><span id="o-hval-O-HEADER" class="text-amber-400 font-mono font-bold">120px</span></div>
                                    <input type="range" min="60" max="300" step="10" value="120" id="o-height-slider-O-HEADER" oninput="onOrdersSliderInput('O-HEADER', 'height', this.value)" class="w-full accent-amber-500">
                                </div>
                            </div>
                            <div class="text-[11px] font-mono text-amber-400 pt-1.5 border-t border-slate-700/60" id="o-size-display-O-HEADER">900 × 120 px</div>
                        </div>
                    </div>
                @elseif($sectionId === 'O-KPI')
                    <div data-orders-section="O-KPI" data-widget-wrapper="O-KPI" class="orders-component-item w-full flex flex-col space-y-3 transition-all relative">
                        <div class="orders-section-drag-handle hidden self-start px-2.5 py-1 rounded-lg bg-amber-500/20 hover:bg-amber-500 text-amber-800 dark:text-amber-200 hover:text-white border border-amber-400/40 cursor-grab active:cursor-grabbing font-mono text-xs font-bold transition-colors select-none shadow-sm mb-1">⠿ GESER BAGIAN: Ringkasan Status (KPI)</div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 items-start w-full" id="orders-kpi-grid">
                            @foreach($sortedKpiList as $kpi)
                                @php $kpiId = $kpi['id']; @endphp
                                <div data-kpi-wrapper="{{ $kpiId }}" class="w-full flex flex-col items-center space-y-3 transition-all">
                                    <div id="orders-card-{{ $kpiId }}" data-component-id="{{ $kpiId }}" class="caterflow-visual-card w-full p-4 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm space-y-1 transition-all relative overflow-hidden flex flex-col justify-between">
                                        <div class="orders-kpi-drag-handle hidden absolute top-2 right-2 z-10 px-2 py-0.5 rounded-md bg-amber-500/20 hover:bg-amber-500 text-amber-800 dark:text-amber-200 hover:text-white border border-amber-400/40 cursor-grab active:cursor-grabbing transition-colors flex items-center space-x-1 select-none shadow-sm" title="Tahan dan geser untuk memindahkan urutan kartu KPI">
                                            <svg class="w-3 h-3 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 8h16M4 16h16"></path></svg>
                                            <span class="text-[9px] font-extrabold font-mono pointer-events-none">GESER</span>
                                        </div>
                                        <div>
                                            <div class="flex items-center justify-between flex-between-header">
                                                <span class="text-[10px] font-bold uppercase text-{{ $kpi['color'] }}-600 dark:text-{{ $kpi['color'] }}-400 tracking-wider adaptive-label truncate">{{ $kpi['label'] }}</span>
                                                <span class="text-sm adaptive-icon">{{ $kpi['icon'] }}</span>
                                            </div>
                                            <div class="text-xl font-extrabold text-slate-900 dark:text-white mt-1 stat-value truncate">{{ $kpi['value'] }}</div>
                                            <span class="text-[10px] text-slate-400 mt-0.5 subtext block truncate">{{ $kpi['subtext'] }}</span>
                                        </div>
                                    </div>
                                    <div class="orders-edit-controls hidden w-full p-3.5 rounded-xl bg-slate-900/90 text-white border border-slate-700/60 shadow-lg space-y-2.5 text-xs backdrop-blur z-20">
                                        <div class="flex items-center justify-between font-bold border-b border-slate-700 pb-1.5 text-[10px]">
                                            <div class="flex items-center space-x-1.5"><span class="orders-kpi-drag-handle cursor-grab px-2 py-0.5 rounded bg-amber-600 hover:bg-amber-500 text-white text-[10px] font-bold">⠿ Geser</span><span class="text-amber-400 font-bold truncate">Atur: {{ $kpi['label'] }}</span></div>
                                            <button type="button" onclick="resetSingleOrdersComponent('{{ $kpiId }}')" class="px-1.5 py-0.5 rounded text-[9px] font-semibold bg-amber-800/80 hover:bg-amber-700 text-white border border-amber-600">🔄 Reset</button>
                                        </div>
                                        <div class="space-y-1">
                                            <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Bentuk</span>
                                            <div class="grid grid-cols-3 gap-1">
                                                @foreach(['rectangle'=>'■ Kotak','rounded'=>'◉ Bulat','sharp'=>'▪ Tajam','pill'=>'⬭ Pil','circle'=>'○ Lingkar','hexagon'=>'⬡ Hex'] as $sId => $sLabel)
                                                    <button type="button" onclick="setOrdersComponentShape('{{ $kpiId }}', '{{ $sId }}')" class="o-shape-btn-{{ $kpiId }} px-1 py-1 rounded text-[9px] font-semibold border text-center bg-white text-slate-800 border-slate-300 hover:bg-amber-500 hover:text-white" data-shape="{{ $sId }}">{{ $sLabel }}</button>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2 gap-1.5">
                                            <div><div class="flex items-center justify-between text-[9px] font-bold text-slate-400 mb-0.5"><span>Lebar</span><span id="o-wval-{{ $kpiId }}" class="text-amber-400 font-mono font-bold">240px</span></div><input type="range" min="140" max="400" step="10" value="240" id="o-width-slider-{{ $kpiId }}" oninput="onOrdersSliderInput('{{ $kpiId }}', 'width', this.value)" class="w-full accent-amber-500"></div>
                                            <div><div class="flex items-center justify-between text-[9px] font-bold text-slate-400 mb-0.5"><span>Tinggi</span><span id="o-hval-{{ $kpiId }}" class="text-amber-400 font-mono font-bold">120px</span></div><input type="range" min="80" max="240" step="10" value="120" id="o-height-slider-{{ $kpiId }}" oninput="onOrdersSliderInput('{{ $kpiId }}', 'height', this.value)" class="w-full accent-amber-500"></div>
                                        </div>
                                        <div class="text-[9px] font-mono text-amber-400 pt-1 border-t border-slate-700/60" id="o-size-display-{{ $kpiId }}">240 × 120 px</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @elseif($sectionId === 'O-FILTER')
                    <div data-orders-section="O-FILTER" data-widget-wrapper="O-FILTER" class="orders-component-item w-full flex flex-col space-y-3 transition-all relative">
                        <div class="orders-section-drag-handle hidden self-start px-2.5 py-1 rounded-lg bg-amber-500/20 hover:bg-amber-500 text-amber-800 dark:text-amber-200 hover:text-white border border-amber-400/40 cursor-grab active:cursor-grabbing font-mono text-xs font-bold transition-colors select-none shadow-sm mb-1">⠿ GESER BAGIAN: Toolbar Filter & Pencarian</div>
                        <div id="orders-card-O-FILTER" data-component-id="O-FILTER" class="caterflow-visual-card w-full p-3 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-3 overflow-hidden transition-all">
                            <div class="flex items-center space-x-1.5 overflow-x-auto text-xs font-semibold pb-1 md:pb-0">
                                <a href="{{ route('orders.index', array_merge(request()->except('status', 'page'), ['status' => 'all'])) }}" class="px-3 py-1.5 rounded-lg {{ !request('status') || request('status') === 'all' ? 'bg-amber-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }} transition-colors">Semua Status</a>
                                <a href="{{ route('orders.index', array_merge(request()->except('status', 'page'), ['status' => 'pending'])) }}" class="px-3 py-1.5 rounded-lg {{ request('status') === 'pending' ? 'bg-amber-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }} transition-colors">Menunggu</a>
                                <a href="{{ route('orders.index', array_merge(request()->except('status', 'page'), ['status' => 'preparing'])) }}" class="px-3 py-1.5 rounded-lg {{ request('status') === 'preparing' ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }} transition-colors">Diproses</a>
                                <a href="{{ route('orders.index', array_merge(request()->except('status', 'page'), ['status' => 'on_delivery'])) }}" class="px-3 py-1.5 rounded-lg {{ request('status') === 'on_delivery' ? 'bg-purple-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }} transition-colors">Dikirim</a>
                                <a href="{{ route('orders.index', array_merge(request()->except('status', 'page'), ['status' => 'completed'])) }}" class="px-3 py-1.5 rounded-lg {{ request('status') === 'completed' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }} transition-colors">Selesai</a>
                            </div>
                            <form method="GET" action="{{ route('orders.index') }}" class="flex items-center space-x-2">
                                @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
                                <div class="relative">
                                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari no. pesanan / alamat..." class="h-8 pl-8 pr-3 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-amber-500 w-48 sm:w-64">
                                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </div>
                                <button type="submit" class="h-8 px-3 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs transition-colors shadow-sm">Cari</button>
                                @if(request('search') || (request('status') && request('status') !== 'all'))
                                    <a href="{{ route('orders.index') }}" class="h-8 px-2.5 rounded-lg bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 text-slate-600 dark:text-slate-300 font-semibold text-xs flex items-center justify-center transition-colors">✕</a>
                                @endif
                            </form>
                        </div>
                        <div class="orders-edit-controls hidden w-full p-4 rounded-xl bg-slate-900/90 text-white border border-slate-700/60 shadow-lg space-y-3 text-xs backdrop-blur z-20">
                            <div class="flex items-center justify-between font-bold border-b border-slate-700 pb-2 text-xs">
                                <span class="text-amber-400 font-bold">Atur Komponen: Filter Toolbar (O-FILTER)</span>
                                <button type="button" onclick="resetSingleOrdersComponent('O-FILTER')" class="px-2 py-1 rounded text-xs font-semibold bg-amber-800/80 hover:bg-amber-700 text-white border border-amber-600 transition-colors" title="Reset komponen ini">🔄 Reset</button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1.5">Bentuk</span>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach(['rectangle'=>'■ Kotak','rounded'=>'◉ Bulat','sharp'=>'▪ Tajam','pill'=>'⬭ Pil','circle'=>'○ Lingkar','hexagon'=>'⬡ Hex'] as $sId => $sLabel)
                                            <button type="button" onclick="setOrdersComponentShape('O-FILTER', '{{ $sId }}')" class="o-shape-btn-O-FILTER px-2.5 py-1.5 rounded-lg text-xs font-semibold border bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-600 text-slate-800 dark:text-slate-200 hover:bg-amber-500 hover:text-white transition-colors" data-shape="{{ $sId }}">{{ $sLabel }}</button>
                                        @endforeach
                                    </div>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5"><span>Lebar</span><span id="o-wval-O-FILTER" class="text-amber-400 font-mono font-bold">900px</span></div>
                                    <input type="range" min="240" max="900" step="10" value="900" id="o-width-slider-O-FILTER" oninput="onOrdersSliderInput('O-FILTER', 'width', this.value)" class="w-full accent-amber-500">
                                </div>
                                <div>
                                    <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5"><span>Tinggi</span><span id="o-hval-O-FILTER" class="text-amber-400 font-mono font-bold">80px</span></div>
                                    <input type="range" min="50" max="250" step="10" value="80" id="o-height-slider-O-FILTER" oninput="onOrdersSliderInput('O-FILTER', 'height', this.value)" class="w-full accent-amber-500">
                                </div>
                            </div>
                            <div class="text-[11px] font-mono text-amber-400 pt-1.5 border-t border-slate-700/60" id="o-size-display-O-FILTER">900 × 80 px</div>
                        </div>
                    </div>
                @elseif($sectionId === 'O-LIST')
                    <div data-orders-section="O-LIST" data-widget-wrapper="O-LIST" class="orders-component-item w-full flex flex-col space-y-3 transition-all relative">
                        <div class="orders-section-drag-handle hidden self-start px-2.5 py-1 rounded-lg bg-amber-500/20 hover:bg-amber-500 text-amber-800 dark:text-amber-200 hover:text-white border border-amber-400/40 cursor-grab active:cursor-grabbing font-mono text-xs font-bold transition-colors select-none shadow-sm mb-1">⠿ GESER BAGIAN: Daftar Riwayat Pesanan</div>
                        <div id="orders-card-O-LIST" data-component-id="O-LIST" class="caterflow-visual-card w-full p-4 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4 overflow-hidden transition-all">
                            <div class="space-y-3" id="orders-list-container">
                                @forelse($orders as $order)
                                    @php
                                        $statusStr = is_object($order->status) && property_exists($order->status, 'value') ? $order->status->value : (string)$order->status;
                                        $statusLabel = match($statusStr) {
                                            'completed', 'selesai' => 'Selesai',
                                            'preparing', 'processing', 'sedang_diproses', 'sedang_dimasak' => 'Diproses Dapur',
                                            'on_delivery', 'sedang_dikirim' => 'Dikirim Kurir',
                                            'pending', 'menunggu_pembayaran', 'menunggu_verifikasi' => 'Menunggu Pembayaran',
                                            default => ucfirst(str_replace('_', ' ', $statusStr))
                                        };
                                        $badgeClass = match($statusStr) {
                                            'completed', 'selesai' => 'bg-emerald-100 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                                            'preparing', 'processing', 'sedang_diproses', 'sedang_dimasak' => 'bg-blue-100 dark:bg-blue-950/40 text-blue-800 dark:text-blue-300 border-blue-200 dark:border-blue-800',
                                            'on_delivery', 'sedang_dikirim' => 'bg-purple-100 dark:bg-purple-950/40 text-purple-800 dark:text-purple-300 border-purple-200 dark:border-purple-800',
                                            'pending', 'menunggu_pembayaran', 'menunggu_verifikasi' => 'bg-amber-100 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                                            default => 'bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border-slate-200 dark:border-slate-700'
                                        };
                                    @endphp
                                    <div data-order-wrapper="{{ $order->id }}" class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:border-amber-300 transition-colors relative">
                                        @if(auth()->user()->isAdmin())
                                            <div class="orders-item-drag-handle hidden absolute top-2 right-2 z-10 px-2 py-0.5 rounded-md bg-amber-500/20 hover:bg-amber-500 text-amber-800 dark:text-amber-200 hover:text-white border border-amber-400/40 cursor-grab active:cursor-grabbing transition-colors flex items-center space-x-1 select-none shadow-sm" title="Tahan dan geser untuk memindahkan urutan kartu pesanan">
                                                <svg class="w-3 h-3 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 8h16M4 16h16"></path></svg>
                                                <span class="text-[9px] font-extrabold font-mono pointer-events-none">GESER</span>
                                            </div>
                                        @endif
                                        <div class="space-y-1.5 flex-1 pr-12 md:pr-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="text-sm font-extrabold text-slate-900 dark:text-white font-mono">{{ $order->order_number }}</span>
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold border {{ $badgeClass }}">{{ $statusLabel }}</span>
                                                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">({{ $order->items->count() }} Variasi Menu)</span>
                                            </div>
                                            <div class="text-xs text-slate-600 dark:text-slate-400 space-y-0.5">
                                                <p class="font-semibold text-slate-800 dark:text-slate-200 flex items-center space-x-1">
                                                    <span>👤 {{ $order->user?->name ?? 'Pelanggan' }}</span>
                                                    @if($order->user?->profile?->company_name)
                                                        <span class="text-slate-400">• {{ $order->user->profile->company_name }}</span>
                                                    @endif
                                                </p>
                                                <p>📅 Dibuat: {{ $order->created_at->format('d M Y, H:i') }} WIB • 📍 Alamat: {{ Str::limit($order->delivery_address, 65) }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between md:flex-col md:items-end gap-2 border-t md:border-t-0 pt-3 md:pt-0 border-slate-100 dark:border-slate-800 shrink-0">
                                            <div class="text-base font-black text-amber-600 dark:text-amber-400">{{ format_idr($order->grand_total ?? $order->subtotal ?? 0) }}</div>
                                            <a href="{{ route('orders.show', $order->id) }}" class="px-4 py-2 rounded-xl bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs hover:bg-amber-500 hover:text-white transition-colors border border-slate-200 dark:border-slate-700 shadow-sm">Lihat Detail & Lacak Pesanan ➔</a>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-12 text-center rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-400 text-xs">Belum ada riwayat pesanan katering.</div>
                                @endforelse
                            </div>
                        </div>
                        <div class="orders-edit-controls hidden w-full p-4 rounded-xl bg-slate-900/90 text-white border border-slate-700/60 shadow-lg space-y-3 text-xs backdrop-blur z-20">
                            <div class="flex items-center justify-between font-bold border-b border-slate-700 pb-2 text-xs">
                                <span class="text-amber-400 font-bold">Atur Komponen: Daftar Riwayat Pesanan (O-LIST)</span>
                                <button type="button" onclick="resetSingleOrdersComponent('O-LIST')" class="px-2 py-1 rounded text-xs font-semibold bg-amber-800/80 hover:bg-amber-700 text-white border border-amber-600 transition-colors" title="Reset komponen ini">🔄 Reset</button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1.5">Bentuk</span>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach(['rectangle'=>'■ Kotak','rounded'=>'◉ Bulat','sharp'=>'▪ Tajam','pill'=>'⬭ Pil','circle'=>'○ Lingkar','hexagon'=>'⬡ Hex'] as $sId => $sLabel)
                                            <button type="button" onclick="setOrdersComponentShape('O-LIST', '{{ $sId }}')" class="o-shape-btn-O-LIST px-2.5 py-1.5 rounded-lg text-xs font-semibold border bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-600 text-slate-800 dark:text-slate-200 hover:bg-amber-500 hover:text-white transition-colors" data-shape="{{ $sId }}">{{ $sLabel }}</button>
                                        @endforeach
                                    </div>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5"><span>Lebar</span><span id="o-wval-O-LIST" class="text-amber-400 font-mono font-bold">900px</span></div>
                                    <input type="range" min="240" max="900" step="10" value="900" id="o-width-slider-O-LIST" oninput="onOrdersSliderInput('O-LIST', 'width', this.value)" class="w-full accent-amber-500">
                                </div>
                                <div>
                                    <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5"><span>Tinggi</span><span id="o-hval-O-LIST" class="text-amber-400 font-mono font-bold">450px</span></div>
                                    <input type="range" min="150" max="800" step="10" value="450" id="o-height-slider-O-LIST" oninput="onOrdersSliderInput('O-LIST', 'height', this.value)" class="w-full accent-amber-500">
                                </div>
                            </div>
                            <div class="text-[11px] font-mono text-amber-400 pt-1.5 border-t border-slate-700/60" id="o-size-display-O-LIST">900 × 450 px</div>
                        </div>
                    </div>
                @elseif($sectionId === 'O-PAGINATION')
                    <div data-orders-section="O-PAGINATION" data-widget-wrapper="O-PAGINATION" class="orders-component-item w-full flex flex-col space-y-3 transition-all relative">
                        <div class="orders-section-drag-handle hidden self-start px-2.5 py-1 rounded-lg bg-amber-500/20 hover:bg-amber-500 text-amber-800 dark:text-amber-200 hover:text-white border border-amber-400/40 cursor-grab active:cursor-grabbing font-mono text-xs font-bold transition-colors select-none shadow-sm mb-1">⠿ GESER BAGIAN: Navigasi Halaman (Pagination)</div>
                        <div id="orders-card-O-PAGINATION" data-component-id="O-PAGINATION" class="caterflow-visual-card w-full p-3 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden transition-all">{{ $orders->links() }}</div>
                        <div class="orders-edit-controls hidden w-full p-4 rounded-xl bg-slate-900/90 text-white border border-slate-700/60 shadow-lg space-y-3 text-xs backdrop-blur z-20">
                            <div class="flex items-center justify-between font-bold border-b border-slate-700 pb-2 text-xs">
                                <span class="text-amber-400 font-bold">Atur Komponen: Pagination (O-PAGINATION)</span>
                                <button type="button" onclick="resetSingleOrdersComponent('O-PAGINATION')" class="px-2 py-1 rounded text-xs font-semibold bg-amber-800/80 hover:bg-amber-700 text-white border border-amber-600 transition-colors" title="Reset komponen ini">🔄 Reset</button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1.5">Bentuk</span>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach(['rectangle'=>'■ Kotak','rounded'=>'◉ Bulat','sharp'=>'▪ Tajam','pill'=>'⬭ Pil','circle'=>'○ Lingkar','hexagon'=>'⬡ Hex'] as $sId => $sLabel)
                                            <button type="button" onclick="setOrdersComponentShape('O-PAGINATION', '{{ $sId }}')" class="o-shape-btn-O-PAGINATION px-2.5 py-1.5 rounded-lg text-xs font-semibold border bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-600 text-slate-800 dark:text-slate-200 hover:bg-amber-500 hover:text-white transition-colors" data-shape="{{ $sId }}">{{ $sLabel }}</button>
                                        @endforeach
                                    </div>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5"><span>Lebar</span><span id="o-wval-O-PAGINATION" class="text-amber-400 font-mono font-bold">900px</span></div>
                                    <input type="range" min="240" max="900" step="10" value="900" id="o-width-slider-O-PAGINATION" oninput="onOrdersSliderInput('O-PAGINATION', 'width', this.value)" class="w-full accent-amber-500">
                                </div>
                                <div>
                                    <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5"><span>Tinggi</span><span id="o-hval-O-PAGINATION" class="text-amber-400 font-mono font-bold">60px</span></div>
                                    <input type="range" min="40" max="200" step="10" value="60" id="o-height-slider-O-PAGINATION" oninput="onOrdersSliderInput('O-PAGINATION', 'height', this.value)" class="w-full accent-amber-500">
                                </div>
                            </div>
                            <div class="text-[11px] font-mono text-amber-400 pt-1.5 border-t border-slate-700/60" id="o-size-display-O-PAGINATION">900 × 60 px</div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.ordersIsEditMode        = false;
            window.ordersSectionsOrder     = @json($ordersSectionsOrder);
            window.ordersSectionsLastSaved = [...window.ordersSectionsOrder];
            window.ordersKpiOrder          = @json($ordersKpiOrder);
            window.ordersKpiLastSaved      = [...window.ordersKpiOrder];
            window.ordersSavedStyles       = @json($ordersSavedStyles);
            window.ordersDefaults          = @json($defaultOrdersStyles);
            window.ordersCurrentStyles     = Object.assign({}, window.ordersDefaults, window.ordersSavedStyles);
            window.ordersLastSavedStyles   = JSON.parse(JSON.stringify(window.ordersCurrentStyles));
            window.ordersSectionsSortable  = null;
            window.ordersKpiSortable       = null;

            initOrdersSortables();
            initOrdersComponentStyles();
        });

        function initOrdersSortables() {
            const secContainer = document.getElementById('orders-sections-container');
            if (secContainer && typeof Sortable !== 'undefined') {
                window.ordersSectionsSortable = new Sortable(secContainer, {
                    animation: 250, handle: '.orders-section-drag-handle', draggable: '[data-orders-section]',
                    ghostClass: 'dashboard-sortable-ghost', dragClass: 'dashboard-sortable-drag', chosenClass: 'dashboard-sortable-chosen',
                    disabled: true, onEnd: function() { const newOrder = []; secContainer.querySelectorAll('[data-orders-section]').forEach(el => { newOrder.push(el.dataset.ordersSection); }); window.ordersSectionsOrder = newOrder; }
                });
            }
            const kpiGrid = document.getElementById('orders-kpi-grid');
            if (kpiGrid && typeof Sortable !== 'undefined') {
                window.ordersKpiSortable = new Sortable(kpiGrid, {
                    animation: 200, handle: '.orders-kpi-drag-handle', draggable: '[data-kpi-wrapper]',
                    ghostClass: 'dashboard-sortable-ghost', dragClass: 'dashboard-sortable-drag', chosenClass: 'dashboard-sortable-chosen',
                    disabled: true, onEnd: function() { const newOrder = []; kpiGrid.querySelectorAll('[data-kpi-wrapper]').forEach(el => { newOrder.push(el.dataset.kpiWrapper); }); window.ordersKpiOrder = newOrder; }
                });
            }
        }

        function initOrdersComponentStyles() { Object.keys(window.ordersCurrentStyles).forEach(cId => { applyOrdersComponentStyle(cId, window.ordersCurrentStyles[cId]); }); }

        function toggleOrdersEditMode() {
            window.ordersIsEditMode = !window.ordersIsEditMode;
            const bar = document.getElementById('orders-edit-mode-bar'), btn = document.getElementById('orders-btn-text'), root = document.getElementById('caterflow-orders-root');
            if (window.ordersIsEditMode) {
                bar.classList.remove('hidden'); btn.textContent = '✕ Keluar Mode Penyesuaian'; root.classList.add('in-edit-mode');
                document.querySelectorAll('.orders-edit-controls').forEach(el => el.classList.remove('hidden'));
                document.querySelectorAll('.orders-section-drag-handle').forEach(el => el.classList.remove('hidden'));
                document.querySelectorAll('.orders-kpi-drag-handle').forEach(el => el.classList.remove('hidden'));
                document.querySelectorAll('[data-widget-wrapper]').forEach(el => { el.classList.add('p-1.5', 'rounded-2xl', 'border-2', 'border-dashed', 'border-amber-500/50', 'bg-amber-50/10'); });
            } else {
                bar.classList.add('hidden'); btn.textContent = '⚙️ Sesuaikan Tampilan'; root.classList.remove('in-edit-mode');
                document.querySelectorAll('.orders-edit-controls').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('.orders-section-drag-handle').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('.orders-kpi-drag-handle').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('[data-widget-wrapper]').forEach(el => { el.classList.remove('p-1.5', 'rounded-2xl', 'border-2', 'border-dashed', 'border-amber-500/50', 'bg-amber-50/10'); });
            }
            if (window.ordersSectionsSortable) window.ordersSectionsSortable.option('disabled', !window.ordersIsEditMode);
            if (window.ordersKpiSortable) window.ordersKpiSortable.option('disabled', !window.ordersIsEditMode);
            if (window.ordersIsEditMode) updateOrdersStyleDisplays();
        }

        function setOrdersComponentShape(cId, shape) {
            if (!window.ordersCurrentStyles[cId]) window.ordersCurrentStyles[cId] = {};
            window.ordersCurrentStyles[cId].shape = shape; applyOrdersComponentStyle(cId, window.ordersCurrentStyles[cId]);
            document.querySelectorAll('.o-shape-btn-' + cId).forEach(btn => {
                const isActive = btn.dataset.shape === shape;
                btn.classList.toggle('bg-amber-500', isActive); btn.classList.toggle('text-white', isActive); btn.classList.toggle('border-amber-600', isActive);
                btn.classList.toggle('bg-white', !isActive); btn.classList.toggle('text-slate-800', !isActive); btn.classList.toggle('border-slate-300', !isActive);
            });
        }

        function onOrdersSliderInput(cId, dim, value) { const v = parseInt(value); if (isNaN(v)) return; if (!window.ordersCurrentStyles[cId]) window.ordersCurrentStyles[cId] = {}; window.ordersCurrentStyles[cId][dim] = v; applyOrdersComponentStyle(cId, window.ordersCurrentStyles[cId]); }

        function applyOrdersComponentStyle(cId, style) {
            if (!style) return; const visualCard = document.querySelector('[data-component-id="' + cId + '"]'); if (!visualCard) return;
            const isKpi = ['pending', 'preparing', 'on_delivery', 'completed'].includes(cId);
            const defaultW = isKpi ? 240 : 900, defaultH = isKpi ? 120 : (cId === 'O-LIST' ? 450 : (cId === 'O-PAGINATION' ? 60 : 120)), shape = style.shape || 'rectangle';
            const reqWidth = parseInt(style.width) || defaultW, reqHeight = parseInt(style.height) || defaultH;
            const bounds = { minW: 140, maxW: 900, minH: 40, maxH: 800 }, clampedW = Math.max(bounds.minW, Math.min(bounds.maxW, reqWidth)), clampedH = Math.max(bounds.minH, Math.min(bounds.maxH, reqHeight));
            visualCard.style.cssText = ''; visualCard.style.boxSizing = 'border-box';
            if (shape === 'circle') { const d = Math.min(clampedW, clampedH); visualCard.style.cssText = `width:${d}px;height:${d}px;min-width:${d}px;max-width:${d}px;min-height:${d}px;max-height:${d}px;aspect-ratio:1/1;border-radius:50%;margin:0 auto;align-self:center;box-sizing:border-box;`; }
            else { visualCard.style.width = `${clampedW}px`; visualCard.style.minHeight = `${clampedH}px`; visualCard.style.height = `${clampedH}px`; visualCard.style.maxWidth = '100%';
                switch (shape) { case 'rounded': visualCard.style.borderRadius = '24px'; break; case 'pill': visualCard.style.borderRadius = '9999px'; break; case 'sharp': visualCard.style.borderRadius = '0px'; break; case 'hexagon': visualCard.style.clipPath = 'polygon(25% 0%, 75% 0%, 100% 50%, 75% 100%, 25% 100%, 0% 50%)'; break; default: visualCard.style.borderRadius = `${parseInt(style.border_radius) || 16}px`; } }
            if (window.adaptComponentContent) window.adaptComponentContent(visualCard, shape, clampedW, clampedH);
            const actualRect = visualCard.getBoundingClientRect(), display = document.getElementById('o-size-display-' + cId); if (display) display.textContent = `${Math.round(actualRect.width)} × ${Math.round(actualRect.height)} px (Req: ${reqWidth}×${reqHeight})`;
            const wS = document.getElementById('o-width-slider-' + cId), hS = document.getElementById('o-height-slider-' + cId), wV = document.getElementById('o-wval-' + cId), hV = document.getElementById('o-hval-' + cId);
            if (wS) wS.value = reqWidth; if (hS) hS.value = reqHeight; if (wV) wV.textContent = `${reqWidth}px`; if (hV) hV.textContent = `${reqHeight}px`;
        }

        function updateOrdersStyleDisplays() {
            Object.keys(window.ordersCurrentStyles).forEach(cId => {
                const style = window.ordersCurrentStyles[cId]; if (!style) return;
                const isKpi = ['pending', 'preparing', 'on_delivery', 'completed'].includes(cId), defaultW = isKpi ? 240 : 900, defaultH = isKpi ? 120 : (cId === 'O-LIST' ? 450 : (cId === 'O-PAGINATION' ? 60 : 120));
                const w = style.width || defaultW, h = style.height || defaultH;
                const wS = document.getElementById('o-width-slider-' + cId), hS = document.getElementById('o-height-slider-' + cId), wV = document.getElementById('o-wval-' + cId), hV = document.getElementById('o-hval-' + cId);
                if (wS) wS.value = w; if (hS) hS.value = h; if (wV) wV.textContent = `${w}px`; if (hV) hV.textContent = `${h}px`; if (style.shape) setOrdersComponentShape(cId, style.shape);
            });
        }

        function saveOrdersLayout() {
            fetch('{{ route("workspace.save-layout") }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }, body: JSON.stringify({ layout_matrix: { orders_sections_order: window.ordersSectionsOrder, orders_kpi_order: window.ordersKpiOrder, component_styles: { orders: JSON.parse(JSON.stringify(window.ordersCurrentStyles)) } } }) })
            .then(res => res.json()).then(data => { if (data.status === 'success') { window.ordersLastSavedStyles = JSON.parse(JSON.stringify(window.ordersCurrentStyles)); window.ordersSectionsLastSaved = [...window.ordersSectionsOrder]; window.ordersKpiLastSaved = [...window.ordersKpiOrder]; showOrdersToast('✓ Layout riwayat & pelacakan pesanan berhasil disimpan.', 'success'); toggleOrdersEditMode(); } else showOrdersToast('Gagal menyimpan layout: ' + (data.message || 'Error'), 'error'); }).catch(() => showOrdersToast('Kesalahan koneksi saat menyimpan layout.', 'error'));
        }

        function cancelOrdersLayout() {
            window.ordersCurrentStyles = JSON.parse(JSON.stringify(window.ordersLastSavedStyles)); window.ordersSectionsOrder = [...window.ordersSectionsLastSaved]; window.ordersKpiOrder = [...window.ordersKpiLastSaved];
            const secContainer = document.getElementById('orders-sections-container'); if (secContainer) { const secs = {}; secContainer.querySelectorAll('[data-orders-section]').forEach(s => { secs[s.dataset.ordersSection] = s; }); window.ordersSectionsOrder.forEach(secId => { if (secs[secId]) secContainer.appendChild(secs[secId]); }); }
            const kpiGrid = document.getElementById('orders-kpi-grid'); if (kpiGrid) { const kpis = {}; kpiGrid.querySelectorAll('[data-kpi-wrapper]').forEach(k => { kpis[k.dataset.kpiWrapper] = k; }); window.ordersKpiOrder.forEach(kId => { if (kpis[kId]) kpiGrid.appendChild(kpis[kId]); }); }
            Object.keys(window.ordersCurrentStyles).forEach(cId => applyOrdersComponentStyle(cId, window.ordersCurrentStyles[cId]));
            showOrdersToast('Perubahan dibatalkan.', 'info'); toggleOrdersEditMode();
        }

        function resetOrdersLayout() {
            if (!confirm('Kembalikan seluruh tampilan pesanan ke susunan default sistem?')) return;
            fetch('{{ route("workspace.reset-layout") }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }, body: JSON.stringify({ page: 'orders' }) })
            .then(res => res.json()).then(data => {
                if (data.status === 'success') {
                    const defaultSecOrder = data.layout_matrix?.orders_sections_order || ['O-HEADER', 'O-KPI', 'O-FILTER', 'O-LIST', 'O-PAGINATION'], defaultKpiOrder = data.layout_matrix?.orders_kpi_order || ['pending', 'preparing', 'on_delivery', 'completed'];
                    window.ordersSectionsOrder = [...defaultSecOrder]; window.ordersSectionsLastSaved = [...defaultSecOrder]; window.ordersKpiOrder = [...defaultKpiOrder]; window.ordersKpiLastSaved = [...defaultKpiOrder];
                    const resetStyles = (data.layout_matrix?.component_styles || {}).orders || window.ordersDefaults; window.ordersCurrentStyles = Object.assign({}, resetStyles); window.ordersLastSavedStyles = JSON.parse(JSON.stringify(window.ordersCurrentStyles));
                    const secContainer = document.getElementById('orders-sections-container'); if (secContainer) { const secs = {}; secContainer.querySelectorAll('[data-orders-section]').forEach(s => { secs[s.dataset.ordersSection] = s; }); window.ordersSectionsOrder.forEach(secId => { if (secs[secId]) secContainer.appendChild(secs[secId]); }); }
                    const kpiGrid = document.getElementById('orders-kpi-grid'); if (kpiGrid) { const kpis = {}; kpiGrid.querySelectorAll('[data-kpi-wrapper]').forEach(k => { kpis[k.dataset.kpiWrapper] = k; }); window.ordersKpiOrder.forEach(kId => { if (kpis[kId]) kpiGrid.appendChild(kpis[kId]); }); }
                    Object.keys(window.ordersCurrentStyles).forEach(cId => applyOrdersComponentStyle(cId, window.ordersCurrentStyles[cId]));
                    showOrdersToast('✓ Seluruh tampilan pesanan dikembalikan ke default.', 'success'); if (window.ordersIsEditMode) toggleOrdersEditMode();
                }
            }).catch(() => showOrdersToast('Gagal melakukan reset layout.', 'error'));
        }

        function resetSingleOrdersComponent(cId) { const isKpi = ['pending', 'preparing', 'on_delivery', 'completed'].includes(cId), defaultW = isKpi ? 240 : 900, defaultH = isKpi ? 120 : (cId === 'O-LIST' ? 450 : (cId === 'O-PAGINATION' ? 60 : 120)), defaults = (window.ordersDefaults && window.ordersDefaults[cId]) || { shape: 'rectangle', width: defaultW, height: defaultH, border_radius: 16 }; window.ordersCurrentStyles[cId] = Object.assign({}, defaults); applyOrdersComponentStyle(cId, window.ordersCurrentStyles[cId]); updateOrdersStyleDisplays(); showOrdersToast('✓ Komponen ' + cId + ' dikembalikan ke default.', 'success'); }
        function showOrdersToast(msg, type = 'success') { const toast = document.getElementById('orders-toast'), msgEl = document.getElementById('orders-toast-message'), icon = document.getElementById('orders-toast-icon'); if (!toast || !msgEl || !icon) return; msgEl.textContent = msg; icon.textContent = type === 'success' ? '✓' : (type === 'error' ? '✕' : 'ℹ'); toast.classList.remove('hidden', 'translate-y-4', 'opacity-0'); setTimeout(() => { toast.classList.add('translate-y-4', 'opacity-0'); setTimeout(() => toast.classList.add('hidden'), 300); }, 3000); }
    </script>
</x-dashboard-layout>
