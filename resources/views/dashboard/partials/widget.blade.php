{{-- WIDGET CONTAINER COMPONENT WITH ADAPTABLE DRAG HANDLE --}}
@php
    $wId = $wId ?? 'W-KPI';
    $slotName = $slotName ?? 'top';

    $widgetMeta = [
        'W-KPI'      => ['title' => 'Ringkasan KPI Utama', 'icon' => '📊'],
        'W-QUICK'    => ['title' => 'Akses Cepat (Toolbar Pintasan)', 'icon' => '⚡'],
        'W-ORDERS'   => ['title' => 'Tabel Pesanan Terbaru', 'icon' => '📦'],
        'W-STATUS'   => ['title' => 'Status Operasional Dapur & Kurir', 'icon' => '👨‍🍳'],
        'W-ACTIVITY' => ['title' => 'Timeline Aktivitas Sistem', 'icon' => '🕒'],
    ];

    $meta = $widgetMeta[$wId] ?? ['title' => 'Widget Dashboard', 'icon' => '📌'];
@endphp

<div id="widget-{{ $wId }}" data-widget-id="{{ $wId }}" class="caterflow-widget transition-all relative rounded-xl group">

    {{-- EDIT MODE DRAG HANDLE HEADER (HIDDEN IN NORMAL MODE) --}}
    <div class="edit-controls hidden p-3 rounded-t-xl bg-slate-900 text-white flex flex-col gap-2 border-b border-slate-700">
        {{-- ROW 1: Drag handle + slot controls --}}
        <div class="flex items-center justify-between text-xs">
            <div class="flex items-center space-x-2">
                <span class="drag-handle cursor-grab active:cursor-grabbing px-2 py-1 bg-slate-800 hover:bg-slate-700 rounded font-mono font-bold text-emerald-400 select-none shadow-sm flex items-center space-x-1" title="Tarik untuk memindahkan widget">
                    <span>⋮⋮</span>
                    <span>Drag</span>
                </span>
                <span class="font-bold text-slate-200">{{ $meta['icon'] }} {{ $meta['title'] }}</span>
                <span class="px-2 py-0.5 rounded text-[10px] uppercase font-semibold bg-emerald-950 text-emerald-300 border border-emerald-800">
                    Slot: {{ strtoupper($slotName) }}
                </span>
            </div>
            <div class="flex items-center space-x-1.5 shrink-0">
                {{-- DIRECTIONAL CONTROLS --}}
                <button type="button" onclick="moveWidgetDirection('{{ $wId }}', 'up')" class="px-2 py-1 bg-slate-800 hover:bg-slate-700 rounded text-slate-300 hover:text-white font-bold" title="Pindah Atas">▲</button>
                <button type="button" onclick="moveWidgetDirection('{{ $wId }}', 'down')" class="px-2 py-1 bg-slate-800 hover:bg-slate-700 rounded text-slate-300 hover:text-white font-bold" title="Pindah Bawah">▼</button>
                <span class="text-slate-600">|</span>
                <button type="button" onclick="moveWidget('{{ $wId }}', 'top')" class="px-2 py-0.5 bg-slate-800 hover:bg-emerald-800 rounded text-[10px] font-semibold text-slate-300 hover:text-white" title="Pindah ke Slot Top">Top</button>
                <button type="button" onclick="moveWidget('{{ $wId }}', 'left')" class="px-2 py-0.5 bg-slate-800 hover:bg-emerald-800 rounded text-[10px] font-semibold text-slate-300 hover:text-white" title="Pindah ke Slot Left">Left</button>
                <button type="button" onclick="moveWidget('{{ $wId }}', 'right')" class="px-2 py-0.5 bg-slate-800 hover:bg-emerald-800 rounded text-[10px] font-semibold text-slate-300 hover:text-white" title="Pindah ke Slot Right">Right</button>
            </div>
        </div>

        {{-- ROW 2: SHAPE & SIZE PANEL --}}
        <div class="widget-style-panel bg-slate-800 rounded-lg p-3 space-y-3 text-xs border border-slate-700">
            {{-- Shape Picker --}}
            <div class="space-y-1.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Bentuk Komponen</span>
                <div class="flex flex-wrap gap-1.5">
                    @php
                        $allShapes = [
                            'rectangle' => ['label' => '■ Persegi Panjang', 'title' => 'Persegi panjang dengan sudut sedikit membulat'],
                            'rounded'   => ['label' => '◉ Membulat',        'title' => 'Sudut sangat membulat'],
                            'sharp'     => ['label' => '▪ Tajam',            'title' => 'Sudut tajam sempurna'],
                            'pill'      => ['label' => '⬭ Pil / Oval',      'title' => 'Bentuk pil memanjang'],
                            'circle'    => ['label' => '○ Lingkaran',        'title' => 'Lingkaran sempurna'],
                            'hexagon'   => ['label' => '⬡ Heksagon',        'title' => 'Bentuk segi enam'],
                        ];
                        $allowedShapes = \App\Models\WorkspacePreference::getCompatibleShapes($wId);
                    @endphp
                    @foreach($allShapes as $shapeId => $shapeMeta)
                        @if(in_array($shapeId, $allowedShapes, true))
                        <button type="button"
                            data-shape="{{ $shapeId }}"
                            onclick="setWidgetShape('{{ $wId }}', '{{ $shapeId }}')"
                            title="{{ $shapeMeta['title'] }}"
                            class="shape-btn-{{ $wId }} px-2.5 py-1.5 rounded-md text-[10px] font-semibold border transition-colors bg-slate-700 border-slate-600 text-slate-300 hover:bg-emerald-900 hover:border-emerald-600 hover:text-emerald-300"
                        >{{ $shapeMeta['label'] }}</button>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- Size Controls --}}
            <div class="grid grid-cols-2 gap-3">
                {{-- Width --}}
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Lebar</span>
                        <div class="flex items-center space-x-1">
                            <input type="number"
                                id="width-input-{{ $wId }}"
                                data-widget="{{ $wId }}"
                                data-dim="width"
                                min="240" max="800" step="10"
                                value="320"
                                oninput="onSizeInput('{{ $wId }}', 'width', this.value)"
                                class="w-16 px-1.5 py-1 rounded bg-slate-700 border border-slate-600 text-white text-[11px] text-right focus:border-emerald-500 focus:outline-none"
                            >
                            <span class="text-slate-500 text-[10px]">px</span>
                        </div>
                    </div>
                    <input type="range"
                        id="width-slider-{{ $wId }}"
                        data-widget="{{ $wId }}"
                        data-dim="width"
                        min="240" max="800" step="10"
                        value="320"
                        oninput="onSliderInput('{{ $wId }}', 'width', this.value)"
                        class="w-full h-1.5 accent-emerald-500 cursor-pointer"
                    >
                </div>
                {{-- Height --}}
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tinggi</span>
                        <div class="flex items-center space-x-1">
                            <input type="number"
                                id="height-input-{{ $wId }}"
                                data-widget="{{ $wId }}"
                                data-dim="height"
                                min="120" max="500" step="10"
                                value="180"
                                oninput="onSizeInput('{{ $wId }}', 'height', this.value)"
                                class="w-16 px-1.5 py-1 rounded bg-slate-700 border border-slate-600 text-white text-[11px] text-right focus:border-emerald-500 focus:outline-none"
                            >
                            <span class="text-slate-500 text-[10px]">px</span>
                        </div>
                    </div>
                    <input type="range"
                        id="height-slider-{{ $wId }}"
                        data-widget="{{ $wId }}"
                        data-dim="height"
                        min="120" max="500" step="10"
                        value="180"
                        oninput="onSliderInput('{{ $wId }}', 'height', this.value)"
                        class="w-full h-1.5 accent-emerald-500 cursor-pointer"
                    >
                </div>
            </div>

            {{-- Live Measurement Display & Per-Component Reset --}}
            <div class="flex items-center justify-between pt-1 border-t border-slate-700">
                <div class="flex items-center space-x-2">
                    <span class="text-[10px] text-slate-500">Ukuran aktual:</span>
                    <span id="size-display-{{ $wId }}" class="text-[10px] font-mono font-semibold text-emerald-400">320 × 180 px</span>
                </div>
                <button type="button" onclick="resetSingleDashboardWidget('{{ $wId }}')" class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-800/80 hover:bg-amber-700 text-white border border-amber-600 transition-colors" title="Reset komponen ini ke default">🔄 Reset</button>
            </div>
        </div>
    </div>

    {{-- INNER VISUAL COMPONENT CARD (#widget-card-{wId}) --}}
    <div id="widget-card-{{ $wId }}" class="caterflow-visual-card transition-all w-full h-full max-w-full overflow-hidden">

    {{-- WIDGET CONTENT RENDERING BASED ON WIDGET ID --}}
    @if($wId === 'W-KPI')
        {{-- KPI SUMMARY CARDS --}}
        @if($isCustomerPreset)
            <div class="{{ $gridKpi }}">
                <div class="{{ $kpiCard }} kpi-card-box widget-content-box">
                    <div class="flex items-center justify-between flex-between-header">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 adaptive-label">Pesanan Aktif</span>
                        <svg class="w-4 h-4 text-slate-400 adaptive-icon shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    </div>
                    <div class="stat-body text-center sm:text-left">
                        <div class="{{ $statVal }} stat-value">{{ $active_orders->count() }}</div>
                        <p class="text-[11px] text-slate-500 mt-0.5 subtext">dalam proses</p>
                    </div>
                </div>
                <div class="{{ $kpiCard }} kpi-card-box widget-content-box">
                    <div class="flex items-center justify-between flex-between-header">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 adaptive-label">Total Pesanan</span>
                        <svg class="w-4 h-4 text-slate-400 adaptive-icon shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                    <div class="stat-body text-center sm:text-left">
                        <div class="{{ $statVal }} stat-value">{{ $ctx['order_count'] }}</div>
                        <p class="text-[11px] text-slate-500 mt-0.5 subtext">riwayat transaksi</p>
                    </div>
                </div>
                <div class="{{ $kpiCard }} kpi-card-box widget-content-box">
                    <div class="flex items-center justify-between flex-between-header">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 adaptive-label">Total Pengeluaran</span>
                        <svg class="w-4 h-4 text-slate-400 adaptive-icon shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="stat-body text-center sm:text-left">
                        <div class="{{ $statVal }} stat-value">{{ format_idr($ctx['total_spent']) }}</div>
                        <p class="text-[11px] text-slate-500 mt-0.5 subtext">bulan ini</p>
                    </div>
                </div>
                <div class="{{ $kpiCard }} kpi-card-box widget-content-box">
                    <div class="flex items-center justify-between flex-between-header">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 adaptive-label">Status Profil</span>
                        <svg class="w-4 h-4 text-slate-400 adaptive-icon shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <div class="stat-body text-center sm:text-left">
                        <div class="{{ $statVal }} stat-value">{{ $ctx['profile_completion'] }}%</div>
                        <p class="text-[11px] text-slate-500 mt-0.5 subtext">{{ $ctx['profile_completion'] >= 100 ? 'profil lengkap' : 'perlu diisi' }}</p>
                    </div>
                </div>
            </div>
        @else
            <div class="{{ $gridKpi }}">
                <div class="{{ $kpiCard }} kpi-card-box widget-content-box">
                    <div class="flex items-center justify-between flex-between-header">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 adaptive-label">Pesanan Hari Ini</span>
                        <svg class="w-4 h-4 text-blue-500 adaptive-icon shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    </div>
                    <div class="stat-body text-center sm:text-left">
                        <div class="{{ $statVal }} stat-value text-blue-600 dark:text-blue-400">{{ $analytics['today_orders'] ?? 12 }}</div>
                        <p class="text-[11px] text-slate-500 mt-0.5 subtext">transaksi masuk</p>
                    </div>
                </div>
                <div class="{{ $kpiCard }} kpi-card-box widget-content-box">
                    <div class="flex items-center justify-between flex-between-header">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 adaptive-label">Antrean Dapur</span>
                        <svg class="w-4 h-4 text-amber-500 adaptive-icon shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path></svg>
                    </div>
                    <div class="stat-body text-center sm:text-left">
                        <div class="{{ $statVal }} stat-value text-amber-600 dark:text-amber-400">{{ $analytics['active_kitchen_tasks'] ?? 8 }}</div>
                        <p class="text-[11px] text-slate-500 mt-0.5 subtext">sedang diproses</p>
                    </div>
                </div>
                <div class="{{ $kpiCard }} kpi-card-box widget-content-box">
                    <div class="flex items-center justify-between flex-between-header">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 adaptive-label">Kurir Aktif</span>
                        <svg class="w-4 h-4 text-purple-500 adaptive-icon shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <div class="stat-body text-center sm:text-left">
                        <div class="{{ $statVal }} stat-value text-purple-600 dark:text-purple-400">{{ $analytics['active_deliveries'] ?? 6 }}</div>
                        <p class="text-[11px] text-slate-500 mt-0.5 subtext">dalam pengiriman</p>
                    </div>
                </div>
                <div class="{{ $kpiCard }} kpi-card-box widget-content-box">
                    <div class="flex items-center justify-between flex-between-header">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 adaptive-label">Total Pendapatan</span>
                        <svg class="w-4 h-4 text-emerald-500 adaptive-icon shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="stat-body text-center sm:text-left">
                        <div class="{{ $statVal }} stat-value text-emerald-600 dark:text-emerald-400">{{ format_idr($analytics['total_revenue'] ?? 24500000) }}</div>
                        <p class="text-[11px] text-slate-500 mt-0.5 subtext">lunas terverifikasi</p>
                    </div>
                </div>
            </div>
        @endif

    @elseif($wId === 'W-QUICK')
        {{-- AKSES CEPAT (TOOLBAR CUSTOMIZATION TOOLBAR) --}}
        <div class="{{ $cardBox }} widget-content-box">
            <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 dark:border-slate-800 flex-between-header">
                <div class="flex items-center space-x-2">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 adaptive-label">Akses Cepat (Toolbar)</h2>
                    <span class="edit-controls hidden text-[10px] bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded font-semibold">Toolbar Reorder Active</span>
                </div>
                <span class="text-[11px] text-slate-400 font-medium subtext">Pintasan Fitur Utama</span>
            </div>

            @php
                $qaDictionary = [
                    'catalog'   => ['label' => 'Katalog Menu', 'route' => route('menus.index'), 'desc' => 'Jelajahi paket katering', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                    'cart'      => ['label' => 'Keranjang Belanja', 'route' => route('cart.index'), 'desc' => 'Item siap checkout', 'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'],
                    'orders'    => ['label' => 'Pesanan Saya', 'route' => route('orders.index'), 'desc' => 'Riwayat & pelacakan', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                    'kitchen'   => ['label' => 'Manajemen Dapur', 'route' => route('kitchen.index'), 'desc' => 'Antrean masakan', 'icon' => 'M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z'],
                    'delivery'  => ['label' => 'Pengiriman & Kurir', 'route' => route('delivery.index'), 'desc' => 'Armada & jadwal', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
                    'finance'   => ['label' => 'Keuangan & Tagihan', 'route' => route('finance.index'), 'desc' => 'Faktur & bayar', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                    'analytics' => ['label' => 'Analisis Bisnis', 'route' => route('analytics.index'), 'desc' => 'Laporan statistik', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                    'profile'   => ['label' => 'Profil Saya', 'route' => route('profile.edit'), 'desc' => 'Alamat & akun', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                ];
            @endphp

            <div id="quick-actions-container" class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                @foreach($quickActionsOrder as $actionKey)
                    @if(isset($qaDictionary[$actionKey]))
                        @php $qa = $qaDictionary[$actionKey]; @endphp
                        <div data-action-key="{{ $actionKey }}" class="quick-action-item group relative">
                            <a href="{{ $qa['route'] }}" class="h-12 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-850 hover:border-emerald-500 hover:bg-emerald-50/40 dark:hover:bg-emerald-950/20 transition-all flex items-center space-x-3 shadow-sm">
                                <span class="qa-drag-handle edit-controls hidden cursor-grab active:cursor-grabbing text-slate-400 hover:text-emerald-600 font-mono font-bold text-xs select-none shrink-0" title="Geser tombol pintasan">⋮⋮</span>
                                <svg class="w-4 h-4 text-slate-400 group-hover:text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $qa['icon'] }}"></path></svg>
                                <div class="overflow-hidden">
                                    <span class="text-xs font-semibold text-slate-900 dark:text-white group-hover:text-emerald-600 block truncate">{{ $qa['label'] }}</span>
                                    <span class="text-[11px] text-slate-400 truncate block">{{ $qa['desc'] }}</span>
                                </div>
                            </a>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

    @elseif($wId === 'W-ORDERS')
        {{-- RECENT ORDERS TABLE CARD --}}
        <div class="{{ $cardBox }} widget-content-box">
            <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 dark:border-slate-800 flex-between-header">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 adaptive-label">Pesanan Terbaru</h2>
                <a href="{{ route('orders.index') }}" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline subtext">Lihat Semua →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 text-[10px] font-bold text-slate-400 uppercase tracking-wider hide-on-compact">
                            <th class="py-3 px-3">Invoice</th>
                            <th class="py-3 px-3">Customer</th>
                            <th class="py-3 px-3">Status</th>
                            <th class="py-3 px-3">Total</th>
                            <th class="py-3 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($safeOrders->take(5) as $order)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-3.5 px-3 font-mono font-bold text-slate-900 dark:text-white">{{ $order->order_number }}</td>
                            <td class="py-3.5 px-3 text-slate-700 dark:text-slate-300">{{ $order->user?->name ?? 'Customer' }}</td>
                            <td class="py-3.5 px-3">
                                @php
                                    $stVal = is_object($order->status) ? $order->status->value : $order->status;
                                    $stLabel = is_object($order->status) ? $order->status->label() : $order->status;
                                    $stClass = match($stVal) {
                                        'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'preparing' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'on_delivery' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        default => 'bg-slate-100 text-slate-700 border-slate-200',
                                    };
                                @endphp
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-semibold border {{ $stClass }}">
                                    {{ $stLabel }}
                                </span>
                            </td>
                            <td class="py-3.5 px-3 font-semibold text-slate-900 dark:text-white stat-value">{{ format_idr($order->grand_total) }}</td>
                            <td class="py-3.5 px-3 text-right">
                                <a href="{{ route('orders.show', $order->id) }}" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">Detail →</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400">Belum ada transaksi.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @elseif($wId === 'W-STATUS')
        {{-- STATUS OPERASIONAL CARD --}}
        <div class="{{ $cardBox }} widget-content-box">
            <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 dark:border-slate-800 flex-between-header">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 adaptive-label">Status Operasional</h2>
            </div>
            <div class="space-y-4">
                <div>
                    <div class="flex items-center justify-between text-xs mb-1.5">
                        <span class="font-medium text-slate-700 dark:text-slate-300">Pesanan Baru</span>
                        <span class="font-bold text-slate-900 dark:text-white stat-value">60%</span>
                    </div>
                    <div class="w-full h-2 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                        <div class="h-full bg-emerald-600 rounded-full" style="width: 60%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between text-xs mb-1.5">
                        <span class="font-medium text-slate-700 dark:text-slate-300">Dapur</span>
                        <span class="font-bold text-slate-900 dark:text-white stat-value">85%</span>
                    </div>
                    <div class="w-full h-2 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                        <div class="h-full bg-emerald-600 rounded-full" style="width: 85%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between text-xs mb-1.5">
                        <span class="font-medium text-slate-700 dark:text-slate-300">Kurir</span>
                        <span class="font-bold text-slate-900 dark:text-white stat-value">50%</span>
                    </div>
                    <div class="w-full h-2 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                        <div class="h-full bg-emerald-600 rounded-full" style="width: 50%"></div>
                    </div>
                </div>
            </div>
        </div>

    @elseif($wId === 'W-ACTIVITY')
        {{-- AKTIVITAS TERBARU TIMELINE CARD --}}
        <div class="{{ $cardBox }} widget-content-box">
            <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 dark:border-slate-800 flex-between-header">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 adaptive-label">Aktivitas Terbaru</h2>
            </div>
            <div class="space-y-3.5 text-xs">
                <div class="flex items-start space-x-3">
                    <span class="font-mono text-[11px] text-slate-400 shrink-0 w-11 mt-0.5">08.20</span>
                    <span class="text-slate-700 dark:text-slate-300 font-medium">Pesanan INV-001 masuk</span>
                </div>
                <div class="flex items-start space-x-3">
                    <span class="font-mono text-[11px] text-slate-400 shrink-0 w-11 mt-0.5">08.45</span>
                    <span class="text-slate-700 dark:text-slate-300 font-medium">Pembayaran diverifikasi</span>
                </div>
                <div class="flex items-start space-x-3">
                    <span class="font-mono text-[11px] text-slate-400 shrink-0 w-11 mt-0.5">09.15</span>
                    <span class="text-slate-700 dark:text-slate-300 font-medium">Pesanan masuk dapur</span>
                </div>
                <div class="flex items-start space-x-3">
                    <span class="font-mono text-[11px] text-slate-400 shrink-0 w-11 mt-0.5">09.40</span>
                    <span class="text-slate-700 dark:text-slate-300 font-medium">Kurir mengambil pesanan</span>
                </div>
                @foreach($safeActivities->take(3) as $log)
                <div class="flex items-start space-x-3 pt-2 border-t border-slate-100 dark:border-slate-800/60">
                    <span class="font-mono text-[11px] text-slate-400 shrink-0 w-11 mt-0.5">{{ \Carbon\Carbon::parse($log['performed_at'])->format('H.i') }}</span>
                    <span class="text-slate-600 dark:text-slate-400 text-[11px] subtext">{{ $log['action'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    @endif

    </div> {{-- End Inner Visual Component Card (#widget-card-{wId}) --}}
</div>

