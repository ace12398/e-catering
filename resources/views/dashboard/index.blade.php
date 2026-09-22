<x-dashboard-layout>
    <x-slot name="title">Beranda — CaterFlow</x-slot>

    @php
        $isCustomerPreset = ($ctx['is_customer'] && !$ctx['is_admin']);
        $formattedDate    = \Carbon\Carbon::now()->translatedFormat('l, j F Y');
        $safeActivities   = collect($recent_activities ?? []);
        $safeOrders       = collect($recent_orders ?? []);

        // EFFECTIVE LAYOUT MATRIX
        $effectiveMatrix = $layout_matrix ?? \App\Models\WorkspacePreference::getDefaultLayoutMatrix();
        $dashboardStyles = $effectiveMatrix['component_styles']['dashboard'] ?? [];
        $defaultDashboardStyles = [
            'W-KPI-01'   => ['shape' => 'rectangle', 'width' => 240, 'height' => 120, 'border_radius' => 16],
            'W-KPI-02'   => ['shape' => 'rectangle', 'width' => 240, 'height' => 120, 'border_radius' => 16],
            'W-KPI-03'   => ['shape' => 'rectangle', 'width' => 240, 'height' => 120, 'border_radius' => 16],
            'W-KPI-04'   => ['shape' => 'rectangle', 'width' => 240, 'height' => 120, 'border_radius' => 16],
            'W-QUICK'    => ['shape' => 'rectangle', 'width' => 900, 'height' => 160, 'border_radius' => 16],
            'W-ORDERS'   => ['shape' => 'rectangle', 'width' => 900, 'height' => 320, 'border_radius' => 16],
            'W-STATUS'   => ['shape' => 'rectangle', 'width' => 440, 'height' => 200, 'border_radius' => 16],
            'W-ACTIVITY' => ['shape' => 'rectangle', 'width' => 440, 'height' => 240, 'border_radius' => 16],
        ];

        // 4 KPI DATA DEFINITIONS
        $rawKpiList = $isCustomerPreset ? [
            [
                'id' => 'W-KPI-01',
                'label' => 'Pesanan Aktif',
                'icon' => '📦',
                'value' => (string)(is_countable($active_orders) ? count($active_orders) : collect($active_orders ?? [])->count()),
                'subtext' => 'dalam proses',
                'color' => 'blue',
            ],
            [
                'id' => 'W-KPI-02',
                'label' => 'Total Pesanan',
                'icon' => '📋',
                'value' => (string)($ctx['order_count'] ?? 0),
                'subtext' => 'riwayat transaksi',
                'color' => 'amber',
            ],
            [
                'id' => 'W-KPI-03',
                'label' => 'Total Pengeluaran',
                'icon' => '💰',
                'value' => format_idr($ctx['total_spent'] ?? 0),
                'subtext' => 'bulan ini',
                'color' => 'purple',
            ],
            [
                'id' => 'W-KPI-04',
                'label' => 'Status Profil',
                'icon' => '👤',
                'value' => ($ctx['profile_completion'] ?? 80) . '%',
                'subtext' => ($ctx['profile_completion'] ?? 80) >= 100 ? 'profil lengkap' : 'perlu diisi',
                'color' => 'emerald',
            ],
        ] : [
            [
                'id' => 'W-KPI-01',
                'label' => 'Pesanan Hari Ini',
                'icon' => '📦',
                'value' => (string)($analytics['today_orders'] ?? 12),
                'subtext' => 'transaksi masuk',
                'color' => 'blue',
            ],
            [
                'id' => 'W-KPI-02',
                'label' => 'Antrean Dapur',
                'icon' => '🍳',
                'value' => (string)($analytics['active_kitchen_tasks'] ?? 8),
                'subtext' => 'sedang diproses',
                'color' => 'amber',
            ],
            [
                'id' => 'W-KPI-03',
                'label' => 'Kurir Aktif',
                'icon' => '🛵',
                'value' => (string)($analytics['active_deliveries'] ?? 6),
                'subtext' => 'dalam pengiriman',
                'color' => 'purple',
            ],
            [
                'id' => 'W-KPI-04',
                'label' => 'Total Pendapatan',
                'icon' => '💰',
                'value' => format_idr($analytics['total_revenue'] ?? 24500000),
                'subtext' => 'lunas terverifikasi',
                'color' => 'emerald',
            ],
        ];

        $kpiMap = collect($rawKpiList)->keyBy('id');
        $defaultKpiOrder = ['W-KPI-01', 'W-KPI-02', 'W-KPI-03', 'W-KPI-04'];
        $savedKpiOrder = $effectiveMatrix['dashboard_kpi_order'] ?? $defaultKpiOrder;
        $dashboardKpiOrder = array_values(array_unique(array_merge(
            is_array($savedKpiOrder) ? $savedKpiOrder : [],
            $defaultKpiOrder
        )));

        $quickActionsOrder = $effectiveMatrix['quick_actions_order'] ?? ['catalog', 'cart', 'orders', 'kitchen', 'delivery', 'finance', 'analytics', 'profile'];
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

    <style>
        /* Drag-and-Drop Sortable Visual Feedback & Dropzone (Canonical Reference /finance) */
        .dashboard-sortable-ghost {
            opacity: 0.35 !important;
            border: 2px dashed #f59e0b !important;
            background-color: rgba(245, 158, 11, 0.12) !important;
            border-radius: 1.25rem !important;
            box-shadow: inset 0 0 16px rgba(245, 158, 11, 0.25) !important;
            transform: scale(0.98);
        }
        .dashboard-sortable-chosen {
            cursor: grabbing !important;
        }
        .dashboard-sortable-chosen * {
            cursor: grabbing !important;
        }
        .dashboard-sortable-drag {
            opacity: 0.95 !important;
            transform: rotate(1.5deg) scale(1.03) !important;
            box-shadow: 0 25px 30px -5px rgba(0, 0, 0, 0.3), 0 15px 15px -5px rgba(0, 0, 0, 0.2) !important;
            z-index: 9999 !important;
        }
        .dashboard-kpi-drag-handle, .quick-action-drag-handle {
            user-select: none;
            touch-action: none;
        }
    </style>

    <div class="max-w-7xl mx-auto space-y-6 relative" id="caterflow-dashboard-root">

        {{-- TOAST NOTIFICATION --}}
        <div id="toast-notification" class="hidden fixed bottom-6 right-6 z-50 p-4 rounded-xl bg-slate-900 text-white shadow-2xl border border-slate-700 flex items-center space-x-3 transition-all transform translate-y-4 opacity-0">
            <span id="toast-icon" class="text-amber-400 text-lg font-bold">✓</span>
            <span id="toast-message" class="text-xs font-medium">Layout berhasil diperbarui.</span>
        </div>

        {{-- EDIT LAYOUT MODE TOP BAR (CANONICAL AMBER BANNER) --}}
        <div id="edit-mode-bar" class="hidden sticky top-4 z-40 p-4 rounded-2xl bg-amber-950/90 dark:bg-amber-950/95 backdrop-blur border-2 border-amber-500 text-white shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-4 transition-all">
            <div class="flex items-center space-x-3">
                <span class="p-2.5 rounded-xl bg-amber-500/20 border border-amber-400/30 text-amber-300 font-mono text-base">✨</span>
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-300">Mode Penyesuaian Tampilan Aktif — Beranda</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500 text-slate-950">Drag & Drop Aktif</span>
                    </div>
                    <p class="text-xs text-amber-100/90 mt-0.5">Ubah bentuk (shape), lebar, tinggi, dan geser komponen untuk mengubah susunan tata letak secara langsung.</p>
                </div>
            </div>
            <div class="flex items-center space-x-2 shrink-0">
                <button type="button" onclick="cancelDashboardLayout()" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs transition-colors border border-slate-600">
                    ✕ Batal
                </button>
                <button type="button" onclick="resetDashboardLayout()" class="px-3.5 py-2 rounded-xl bg-amber-900/80 hover:bg-amber-800 text-amber-200 font-semibold text-xs transition-colors border border-amber-600">
                    🔄 Reset Default
                </button>
                <button type="button" onclick="saveDashboardLayout()" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold text-xs shadow-md transition-colors flex items-center space-x-1.5">
                    <span>💾 Simpan Tampilan</span>
                </button>
            </div>
        </div>

        {{-- DASHBOARD HEADER --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-6 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm gap-4">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-amber-600 dark:text-amber-400 font-bold">Beranda</span>
                <h1 class="text-xl md:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight mt-0.5">
                    Selamat Datang, {{ $ctx['user_name'] }}!
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center space-x-2">
                    <span>📅 {{ $formattedDate }}</span>
                    <span>•</span>
                    <span>Ringkasan operasional katering & kustomisasi antarmuka.</span>
                </p>
            </div>
            <div class="flex items-center space-x-3 shrink-0">
                <span class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-300 shadow-sm">
                    Peran: {{ $ctx['role_label'] }}
                </span>
                <button type="button" id="btn-toggle-edit" onclick="toggleEditMode()" class="h-9 px-4 rounded-lg bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs transition-colors flex items-center space-x-2 shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    <span id="btn-toggle-text">⚙️ Sesuaikan Tampilan</span>
                </button>
            </div>
        </div>

        {{-- 1. SECTION: 4 DRAGGABLE & CUSTOMIZABLE KPI CARDS --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-start" id="dashboard-kpi-grid">
            @foreach($dashboardKpiOrder as $kpiId)
                @if(isset($kpiMap[$kpiId]))
                    @php $kpi = $kpiMap[$kpiId]; @endphp
                    <div data-kpi-wrapper="{{ $kpi['id'] }}" class="w-full flex flex-col items-center space-y-2 transition-all">
                        <div data-component-id="{{ $kpi['id'] }}" class="dashboard-component-card w-full p-5 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm space-y-1 transition-all relative overflow-hidden flex flex-col justify-between">
                            {{-- Dedicated Card Drag Handle Badge (Edit Mode Only) --}}
                            <div class="dashboard-kpi-drag-handle hidden absolute top-2 right-2 z-10 px-2 py-0.5 rounded-md bg-amber-500/20 hover:bg-amber-500 text-amber-800 dark:text-amber-200 hover:text-white border border-amber-400/40 cursor-grab active:cursor-grabbing transition-colors flex items-center space-x-1 select-none shadow-sm"
                                 title="Tahan dan geser untuk memindahkan urutan kartu">
                                <svg class="w-3 h-3 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 8h16M4 16h16"></path>
                                </svg>
                                <span class="text-[9px] font-extrabold font-mono pointer-events-none">GESER</span>
                            </div>

                            <div>
                                <div class="flex items-center justify-between flex-between-header">
                                    <span class="text-[11px] font-semibold text-slate-500 uppercase block adaptive-label truncate">{{ $kpi['label'] }}</span>
                                    <span class="text-sm adaptive-icon">{{ $kpi['icon'] }}</span>
                                </div>
                                <div class="text-2xl font-extrabold text-slate-900 dark:text-white stat-value mt-1.5 truncate" title="{{ $kpi['value'] }}">
                                    {{ $kpi['value'] }}
                                </div>
                                <span class="text-[11px] text-{{ $kpi['color'] }}-600 font-semibold subtext block mt-0.5 truncate">
                                    {{ $kpi['subtext'] }}
                                </span>
                            </div>
                        </div>

                        {{-- IN-PLACE STYLE & SIZE PANEL (Edit Mode Only) --}}
                        <div class="dashboard-style-panel hidden w-full p-3 rounded-xl bg-slate-900/90 text-white border border-slate-700/60 shadow-lg space-y-2 text-xs backdrop-blur z-20">
                            <div class="flex items-center justify-between font-bold border-b border-slate-700 pb-1.5 text-[10px]">
                                <div class="flex items-center space-x-1.5">
                                    <span class="dashboard-kpi-drag-handle cursor-grab active:cursor-grabbing px-2 py-0.5 rounded bg-amber-600 hover:bg-amber-500 text-white text-[10px] font-bold flex items-center space-x-1 shadow-sm transition-colors select-none"
                                          title="Tahan dan geser untuk memindahkan urutan kartu">
                                        <span>⠿</span>
                                        <span>Geser</span>
                                    </span>
                                    <span class="text-amber-400 font-bold">Atur: {{ $kpi['label'] }}</span>
                                </div>
                                <span class="text-[9px] text-slate-400 font-mono">({{ $kpi['id'] }})</span>
                            </div>
                            <div class="space-y-1">
                                <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Bentuk</span>
                                <div class="grid grid-cols-3 gap-1">
                                    @foreach(['rectangle'=>'■ Kotak','rounded'=>'◉ Bulat','sharp'=>'▪ Tajam','pill'=>'⬭ Pil','circle'=>'○ Lingkar','hexagon'=>'⬡ Hex'] as $sId => $sLabel)
                                        <button type="button" data-shape="{{ $sId }}"
                                            onclick="setDashboardStatShape('{{ $kpi['id'] }}', '{{ $sId }}')"
                                            class="d-shape-btn-{{ $kpi['id'] }} px-1 py-1 rounded text-[9px] font-semibold border text-center transition-colors bg-white text-slate-800 border-slate-300 hover:bg-amber-500 hover:text-white"
                                        >{{ $sLabel }}</button>
                                    @endforeach
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-1.5">
                                <div>
                                    <div class="flex items-center justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                                        <span>Lebar</span>
                                        <span id="d-wval-{{ $kpi['id'] }}" class="text-amber-400">240px</span>
                                    </div>
                                    <input type="range" id="d-width-{{ $kpi['id'] }}" min="140" max="400" step="10" value="240"
                                        oninput="onDashboardSizeInput('{{ $kpi['id'] }}', 'width', this.value)"
                                        class="w-full accent-amber-500">
                                </div>
                                <div>
                                    <div class="flex items-center justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                                        <span>Tinggi</span>
                                        <span id="d-hval-{{ $kpi['id'] }}" class="text-amber-400">120px</span>
                                    </div>
                                    <input type="range" id="d-height-{{ $kpi['id'] }}" min="80" max="300" step="10" value="120"
                                        oninput="onDashboardSizeInput('{{ $kpi['id'] }}', 'height', this.value)"
                                        class="w-full accent-amber-500">
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-[9px] font-mono font-semibold text-amber-400 pt-1 border-t border-slate-700">
                                <span id="d-size-display-{{ $kpi['id'] }}">240 × 120 px</span>
                                <button type="button" onclick="resetSingleDashboardWidget('{{ $kpi['id'] }}')" class="px-1.5 py-0.5 rounded text-[9px] font-semibold bg-amber-800/80 hover:bg-amber-700 text-white border border-amber-600 transition-colors" title="Reset komponen ini">🔄 Reset</button>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        {{-- 2. SECTION: MAIN CONTENT GRID (W-QUICK, W-ORDERS, W-STATUS, W-ACTIVITY) --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

            {{-- LEFT COLUMN (2/3) --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- W-QUICK (AKSES CEPAT TOOLBAR) --}}
                <div data-widget-wrapper="W-QUICK" class="w-full flex flex-col space-y-3 transition-all">
                    <div data-component-id="W-QUICK" class="dashboard-component-card w-full p-6 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4 transition-all relative overflow-hidden">
                        <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 dark:border-slate-800 flex-between-header">
                            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 adaptive-label">Akses Cepat (Toolbar Pintasan)</h2>
                            <span class="text-[11px] text-slate-400 font-medium subtext">Pintasan Fitur Utama</span>
                        </div>

                        <div id="quick-actions-container" class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            @foreach($quickActionsOrder as $actionKey)
                                @if(isset($qaDictionary[$actionKey]))
                                    @php $qa = $qaDictionary[$actionKey]; @endphp
                                    <div data-action-key="{{ $actionKey }}" class="quick-action-item group relative">
                                        <a href="{{ $qa['route'] }}" class="h-12 px-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-850 hover:border-amber-500 hover:bg-amber-50/40 dark:hover:bg-amber-950/20 transition-all flex items-center space-x-3 shadow-sm">
                                            <svg class="w-4 h-4 text-slate-400 group-hover:text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $qa['icon'] }}"></path></svg>
                                            <div class="overflow-hidden">
                                                <span class="text-xs font-semibold text-slate-900 dark:text-white group-hover:text-amber-600 block truncate">{{ $qa['label'] }}</span>
                                                <span class="text-[11px] text-slate-400 truncate block">{{ $qa['desc'] }}</span>
                                            </div>
                                        </a>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    {{-- SEPARATE RECTANGULAR CONFIGURATION EDITOR (OUTSIDE PREVIEW) --}}
                    <div class="dashboard-style-panel hidden w-full p-4 rounded-xl bg-slate-900/90 text-white border border-slate-700/60 shadow-lg space-y-3 text-xs backdrop-blur z-20">
                        <div class="flex items-center justify-between font-bold border-b border-slate-700 pb-2 text-xs">
                            <span class="text-amber-400 font-bold">Atur Komponen: Akses Cepat (W-QUICK)</span>
                            <button type="button" onclick="resetSingleDashboardWidget('W-QUICK')" class="px-2 py-1 rounded text-xs font-semibold bg-amber-800/80 hover:bg-amber-700 text-white border border-amber-600 transition-colors">🔄 Reset</button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1.5">Bentuk</span>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach(['rectangle'=>'■ Kotak','rounded'=>'◉ Bulat','sharp'=>'▪ Tajam','pill'=>'⬭ Pil'] as $sId => $sLabel)
                                        <button type="button" data-shape="{{ $sId }}" onclick="setDashboardStatShape('W-QUICK', '{{ $sId }}')" class="d-shape-btn-W-QUICK px-2.5 py-1.5 rounded-lg text-xs font-semibold border bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-600 text-slate-800 dark:text-slate-200 hover:bg-amber-500 hover:text-white transition-colors">{{ $sLabel }}</button>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                    <span>Lebar</span>
                                    <span id="d-wval-W-QUICK" class="text-amber-400 font-mono font-bold">900px</span>
                                </div>
                                <input type="range" min="280" max="900" step="10" value="900" id="d-width-W-QUICK" oninput="onDashboardSliderInput('W-QUICK', 'width', this.value)" class="w-full accent-amber-500">
                            </div>
                            <div>
                                <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                    <span>Tinggi</span>
                                    <span id="d-hval-W-QUICK" class="text-amber-400 font-mono font-bold">160px</span>
                                </div>
                                <input type="range" min="120" max="400" step="10" value="160" id="d-height-W-QUICK" oninput="onDashboardSliderInput('W-QUICK', 'height', this.value)" class="w-full accent-amber-500">
                            </div>
                        </div>
                        <div class="text-[11px] font-mono text-amber-400 pt-1.5 border-t border-slate-700/60" id="d-size-display-W-QUICK">900 × 160 px</div>
                    </div>
                </div>

                {{-- W-ORDERS (PESANAN TERBARU) --}}
                <div data-widget-wrapper="W-ORDERS" class="w-full flex flex-col space-y-3 transition-all">
                    <div data-component-id="W-ORDERS" class="dashboard-component-card w-full p-6 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4 transition-all relative overflow-hidden">
                        <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 dark:border-slate-800 flex-between-header">
                            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 adaptive-label">Pesanan Terbaru</h2>
                            <a href="{{ route('orders.index') }}" class="text-xs font-semibold text-amber-600 dark:text-amber-400 hover:underline subtext font-bold">Lihat Semua →</a>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="border-b border-slate-200 dark:border-slate-800 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
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
                                            <a href="{{ route('orders.show', $order->id) }}" class="text-xs font-semibold text-amber-600 dark:text-amber-400 hover:underline">Detail →</a>
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

                    {{-- SEPARATE RECTANGULAR CONFIGURATION EDITOR (OUTSIDE PREVIEW) --}}
                    <div class="dashboard-style-panel hidden w-full p-4 rounded-xl bg-slate-900/90 text-white border border-slate-700/60 shadow-lg space-y-3 text-xs backdrop-blur z-20">
                        <div class="flex items-center justify-between font-bold border-b border-slate-700 pb-2 text-xs">
                            <span class="text-amber-400 font-bold">Atur Komponen: Tabel Pesanan (W-ORDERS)</span>
                            <button type="button" onclick="resetSingleDashboardWidget('W-ORDERS')" class="px-2 py-1 rounded text-xs font-semibold bg-amber-800/80 hover:bg-amber-700 text-white border border-amber-600 transition-colors">🔄 Reset</button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1.5">Bentuk</span>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach(['rectangle'=>'■ Kotak','rounded'=>'◉ Bulat','sharp'=>'▪ Tajam','pill'=>'⬭ Pil'] as $sId => $sLabel)
                                        <button type="button" data-shape="{{ $sId }}" onclick="setDashboardStatShape('W-ORDERS', '{{ $sId }}')" class="d-shape-btn-W-ORDERS px-2.5 py-1.5 rounded-lg text-xs font-semibold border bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-600 text-slate-800 dark:text-slate-200 hover:bg-amber-500 hover:text-white transition-colors">{{ $sLabel }}</button>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                    <span>Lebar</span>
                                    <span id="d-wval-W-ORDERS" class="text-amber-400 font-mono font-bold">900px</span>
                                </div>
                                <input type="range" min="320" max="900" step="10" value="900" id="d-width-W-ORDERS" oninput="onDashboardSliderInput('W-ORDERS', 'width', this.value)" class="w-full accent-amber-500">
                            </div>
                            <div>
                                <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                    <span>Tinggi</span>
                                    <span id="d-hval-W-ORDERS" class="text-amber-400 font-mono font-bold">320px</span>
                                </div>
                                <input type="range" min="200" max="600" step="10" value="320" id="d-height-W-ORDERS" oninput="onDashboardSliderInput('W-ORDERS', 'height', this.value)" class="w-full accent-amber-500">
                            </div>
                        </div>
                        <div class="text-[11px] font-mono text-amber-400 pt-1.5 border-t border-slate-700/60" id="d-size-display-W-ORDERS">900 × 320 px</div>
                    </div>
                </div>

            </div>

            {{-- RIGHT COLUMN (1/3) --}}
            <div class="space-y-6">

                {{-- W-STATUS (STATUS OPERASIONAL / STATUS PESANAN) --}}
                <div data-widget-wrapper="W-STATUS" class="w-full flex flex-col space-y-3 transition-all">
                    <div data-component-id="W-STATUS" class="dashboard-component-card w-full p-6 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4 transition-all relative overflow-hidden">
                        <div class="flex items-center justify-between pb-3.5 border-b border-slate-100 dark:border-slate-800 flex-between-header">
                            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 adaptive-label">
                                {{ $isCustomerPreset ? 'Status Pesanan & Rekomendasi' : 'Status Operasional' }}
                            </h2>
                            @if($isCustomerPreset)
                                <span class="text-[11px] text-amber-600 dark:text-amber-400 font-bold subtext">CaterFlow Care</span>
                            @endif
                        </div>
                        @if($isCustomerPreset)
                            <div class="space-y-4">
                                <div>
                                    <div class="flex items-center justify-between text-xs mb-1.5">
                                        <span class="font-medium text-slate-700 dark:text-slate-300">Pesanan Aktif</span>
                                        <span class="font-bold text-slate-900 dark:text-white stat-value">{{ is_countable($active_orders) ? count($active_orders) : collect($active_orders ?? [])->count() }} Sedang Berjalan</span>
                                    </div>
                                    <div class="w-full h-2 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                                        <div class="h-full bg-amber-500 rounded-full" style="width: {{ count($active_orders ?? []) > 0 ? '75%' : '10%' }}"></div>
                                    </div>
                                </div>
                                <div class="pt-1">
                                    <x-dashboard.recommendation-panel :recommendations="$recommendations ?? []" :profileCompletion="$ctx['profile_completion'] ?? 80" />
                                </div>
                            </div>
                        @else
                            <div class="space-y-4">
                                <div>
                                    <div class="flex items-center justify-between text-xs mb-1.5">
                                        <span class="font-medium text-slate-700 dark:text-slate-300">Pesanan Baru</span>
                                        <span class="font-bold text-slate-900 dark:text-white stat-value">60%</span>
                                    </div>
                                    <div class="w-full h-2 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                                        <div class="h-full bg-amber-500 rounded-full" style="width: 60%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between text-xs mb-1.5">
                                        <span class="font-medium text-slate-700 dark:text-slate-300">Dapur</span>
                                        <span class="font-bold text-slate-900 dark:text-white stat-value">85%</span>
                                    </div>
                                    <div class="w-full h-2 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                                        <div class="h-full bg-amber-500 rounded-full" style="width: 85%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between text-xs mb-1.5">
                                        <span class="font-medium text-slate-700 dark:text-slate-300">Kurir</span>
                                        <span class="font-bold text-slate-900 dark:text-white stat-value">50%</span>
                                    </div>
                                    <div class="w-full h-2 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                                        <div class="h-full bg-amber-500 rounded-full" style="width: 50%"></div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- SEPARATE RECTANGULAR CONFIGURATION EDITOR (OUTSIDE PREVIEW) --}}
                    <div class="dashboard-style-panel hidden w-full p-4 rounded-xl bg-slate-900/90 text-white border border-slate-700/60 shadow-lg space-y-3 text-xs backdrop-blur z-20">
                        <div class="flex items-center justify-between font-bold border-b border-slate-700 pb-2 text-xs">
                            <span class="text-amber-400 font-bold">Atur Komponen: Status (W-STATUS)</span>
                            <button type="button" onclick="resetSingleDashboardWidget('W-STATUS')" class="px-2 py-1 rounded text-xs font-semibold bg-amber-800/80 hover:bg-amber-700 text-white border border-amber-600 transition-colors">🔄 Reset</button>
                        </div>
                        <div class="space-y-2.5">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1.5">Bentuk</span>
                                <div class="grid grid-cols-3 gap-1.5">
                                    @foreach(['rectangle'=>'■ Kotak','rounded'=>'◉ Bulat','sharp'=>'▪ Tajam','pill'=>'⬭ Pil','circle'=>'○ Lingkar','hexagon'=>'⬡ Hex'] as $sId => $sLabel)
                                        <button type="button" data-shape="{{ $sId }}" onclick="setDashboardStatShape('W-STATUS', '{{ $sId }}')" class="d-shape-btn-W-STATUS px-2 py-1.5 rounded-lg text-[11px] font-semibold border text-center bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-600 text-slate-800 dark:text-slate-200 hover:bg-amber-500 hover:text-white transition-colors">{{ $sLabel }}</button>
                                    @endforeach
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                                        <span>Lebar</span>
                                        <span id="d-wval-W-STATUS" class="text-amber-400 font-mono font-bold">440px</span>
                                    </div>
                                    <input type="range" min="200" max="600" step="10" value="440" id="d-width-W-STATUS" oninput="onDashboardSliderInput('W-STATUS', 'width', this.value)" class="w-full accent-amber-500">
                                </div>
                                <div>
                                    <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                                        <span>Tinggi</span>
                                        <span id="d-hval-W-STATUS" class="text-amber-400 font-mono font-bold">200px</span>
                                    </div>
                                    <input type="range" min="140" max="500" step="10" value="200" id="d-height-W-STATUS" oninput="onDashboardSliderInput('W-STATUS', 'height', this.value)" class="w-full accent-amber-500">
                                </div>
                            </div>
                        </div>
                        <div class="text-[11px] font-mono text-amber-400 pt-1.5 border-t border-slate-700/60" id="d-size-display-W-STATUS">440 × 200 px</div>
                    </div>
                </div>

                {{-- W-ACTIVITY (AKTIVITAS TERBARU) --}}
                <div data-widget-wrapper="W-ACTIVITY" class="w-full flex flex-col space-y-3 transition-all">
                    <div data-component-id="W-ACTIVITY" class="dashboard-component-card w-full p-6 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4 transition-all relative overflow-hidden">
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

                    {{-- SEPARATE RECTANGULAR CONFIGURATION EDITOR (OUTSIDE PREVIEW) --}}
                    <div class="dashboard-style-panel hidden w-full p-4 rounded-xl bg-slate-900/90 text-white border border-slate-700/60 shadow-lg space-y-3 text-xs backdrop-blur z-20">
                        <div class="flex items-center justify-between font-bold border-b border-slate-700 pb-2 text-xs">
                            <span class="text-amber-400 font-bold">Atur Komponen: Aktivitas (W-ACTIVITY)</span>
                            <button type="button" onclick="resetSingleDashboardWidget('W-ACTIVITY')" class="px-2 py-1 rounded text-xs font-semibold bg-amber-800/80 hover:bg-amber-700 text-white border border-amber-600 transition-colors">🔄 Reset</button>
                        </div>
                        <div class="space-y-2.5">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1.5">Bentuk</span>
                                <div class="grid grid-cols-3 gap-1.5">
                                    @foreach(['rectangle'=>'■ Kotak','rounded'=>'◉ Bulat','sharp'=>'▪ Tajam','pill'=>'⬭ Pil','circle'=>'○ Lingkar','hexagon'=>'⬡ Hex'] as $sId => $sLabel)
                                        <button type="button" data-shape="{{ $sId }}" onclick="setDashboardStatShape('W-ACTIVITY', '{{ $sId }}')" class="d-shape-btn-W-ACTIVITY px-2 py-1.5 rounded-lg text-[11px] font-semibold border text-center bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-600 text-slate-800 dark:text-slate-200 hover:bg-amber-500 hover:text-white transition-colors">{{ $sLabel }}</button>
                                    @endforeach
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                                        <span>Lebar</span>
                                        <span id="d-wval-W-ACTIVITY" class="text-amber-400 font-mono font-bold">440px</span>
                                    </div>
                                    <input type="range" min="200" max="600" step="10" value="440" id="d-width-W-ACTIVITY" oninput="onDashboardSliderInput('W-ACTIVITY', 'width', this.value)" class="w-full accent-amber-500">
                                </div>
                                <div>
                                    <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                                        <span>Tinggi</span>
                                        <span id="d-hval-W-ACTIVITY" class="text-amber-400 font-mono font-bold">240px</span>
                                    </div>
                                    <input type="range" min="140" max="500" step="10" value="240" id="d-height-W-ACTIVITY" oninput="onDashboardSliderInput('W-ACTIVITY', 'height', this.value)" class="w-full accent-amber-500">
                                </div>
                            </div>
                        </div>
                        <div class="text-[11px] font-mono text-amber-400 pt-1.5 border-t border-slate-700/60" id="d-size-display-W-ACTIVITY">440 × 240 px</div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    {{-- SORTABLE JS LIBRARY --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

    {{-- DASHBOARD ADAPTIVE ENGINE SCRIPT (CANONICAL BENCHMARK /finance) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.isEditMode               = false;
            window.dashboardKpiLastSaved   = @json($dashboardKpiOrder);
            window.dashboardKpiCurrent     = [...window.dashboardKpiLastSaved];
            window.quickActionsLastSaved    = @json($quickActionsOrder);
            window.quickActionsCurrent      = [...window.quickActionsLastSaved];

            window.dashboardDefaults        = @json($defaultDashboardStyles);
            window.dashboardSaved           = @json($dashboardStyles);
            window.currentStyles            = Object.assign({}, window.dashboardDefaults, window.dashboardSaved);
            window.lastSavedStyles          = JSON.parse(JSON.stringify(window.currentStyles));

            window.dashboardKpiSortable     = null;
            window.quickActionsSortable     = null;

            // Initialize Sortable on KPI Grid
            const kpiGrid = document.getElementById('dashboard-kpi-grid');
            if (kpiGrid && typeof Sortable !== 'undefined') {
                window.dashboardKpiSortable = new Sortable(kpiGrid, {
                    handle:     '.dashboard-kpi-drag-handle',
                    draggable:  '[data-kpi-wrapper]',
                    animation:  250,
                    ghostClass: 'dashboard-sortable-ghost',
                    chosenClass:'dashboard-sortable-chosen',
                    dragClass:  'dashboard-sortable-drag',
                    disabled:   true,
                    onEnd: function() {
                        window.dashboardKpiCurrent = Array.from(
                            kpiGrid.querySelectorAll('[data-kpi-wrapper]')
                        ).map(el => el.dataset.kpiWrapper);
                    },
                });
            }

            // Initialize Sortable on Quick Actions
            const qaGrid = document.getElementById('quick-actions-container');
            if (qaGrid && typeof Sortable !== 'undefined') {
                window.quickActionsSortable = new Sortable(qaGrid, {
                    draggable:  '.quick-action-item',
                    animation:  200,
                    ghostClass: 'dashboard-sortable-ghost',
                    chosenClass:'dashboard-sortable-chosen',
                    dragClass:  'dashboard-sortable-drag',
                    disabled:   true,
                    onEnd: function() {
                        window.quickActionsCurrent = Array.from(
                            qaGrid.querySelectorAll('[data-action-key]')
                        ).map(el => el.dataset.actionKey);
                    },
                });
            }

            initDashboardComponentStyles();
        });

        const allDashboardComponentIds = [
            'W-KPI-01', 'W-KPI-02', 'W-KPI-03', 'W-KPI-04',
            'W-QUICK', 'W-ORDERS', 'W-STATUS', 'W-ACTIVITY'
        ];

        function initDashboardComponentStyles() {
            allDashboardComponentIds.forEach(id => {
                const style = window.currentStyles[id];
                if (style) applyDashboardStatStyle(id, style);
            });
        }

        function toggleEditMode() {
            window.isEditMode = !window.isEditMode;
            const bar = document.getElementById('edit-mode-bar');
            const btnText = document.getElementById('btn-toggle-text');
            const root = document.getElementById('caterflow-dashboard-root');

            if (window.isEditMode) {
                bar.classList.remove('hidden');
                btnText.textContent = '✕ Keluar Mode Penyesuaian';
                root.classList.add('in-edit-mode');
                document.querySelectorAll('.dashboard-kpi-drag-handle').forEach(el => el.classList.remove('hidden'));
                document.querySelectorAll('.dashboard-style-panel').forEach(el => el.classList.remove('hidden'));
                document.querySelectorAll('.dashboard-component-card').forEach(el => {
                    el.classList.add('ring-2', 'ring-amber-400/60');
                });
                updateStyleDisplays();
            } else {
                bar.classList.add('hidden');
                btnText.textContent = '⚙️ Sesuaikan Tampilan';
                root.classList.remove('in-edit-mode');
                document.querySelectorAll('.dashboard-kpi-drag-handle').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('.dashboard-style-panel').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('.dashboard-component-card').forEach(el => {
                    el.classList.remove('ring-2', 'ring-amber-400/60');
                });
            }

            if (window.dashboardKpiSortable) {
                window.dashboardKpiSortable.option('disabled', !window.isEditMode);
            }
            if (window.quickActionsSortable) {
                window.quickActionsSortable.option('disabled', !window.isEditMode);
            }
        }

        function saveDashboardLayout() {
            fetch('{{ route("workspace.save-layout") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ layout_matrix: {
                    dashboard_kpi_order: window.dashboardKpiCurrent,
                    quick_actions_order: window.quickActionsCurrent,
                    component_styles: { dashboard: JSON.parse(JSON.stringify(window.currentStyles)) }
                }})
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    window.dashboardKpiLastSaved  = [...window.dashboardKpiCurrent];
                    window.quickActionsLastSaved   = [...window.quickActionsCurrent];
                    window.lastSavedStyles         = JSON.parse(JSON.stringify(window.currentStyles));
                    showToast('✓ Konfigurasi tata letak berhasil disimpan permanen.', 'success');
                    toggleEditMode();
                } else {
                    showToast('Gagal menyimpan layout: ' + (data.message || 'Error'), 'error');
                }
            })
            .catch(() => showToast('Terjadi kesalahan koneksi saat menyimpan layout.', 'error'));
        }

        function cancelDashboardLayout() {
            window.dashboardKpiCurrent = [...window.dashboardKpiLastSaved];
            window.quickActionsCurrent  = [...window.quickActionsLastSaved];
            window.currentStyles        = JSON.parse(JSON.stringify(window.lastSavedStyles));

            // Reorder KPI wrappers in DOM to last saved order
            const kpiGrid = document.getElementById('dashboard-kpi-grid');
            if (kpiGrid) {
                const wrappers = {};
                kpiGrid.querySelectorAll('[data-kpi-wrapper]').forEach(w => { wrappers[w.dataset.kpiWrapper] = w; });
                window.dashboardKpiLastSaved.forEach(key => { if (wrappers[key]) kpiGrid.appendChild(wrappers[key]); });
            }

            // Reorder Quick Actions in DOM to last saved order
            const qaGrid = document.getElementById('quick-actions-container');
            if (qaGrid) {
                const qaItems = {};
                qaGrid.querySelectorAll('[data-action-key]').forEach(w => { qaItems[w.dataset.actionKey] = w; });
                window.quickActionsLastSaved.forEach(key => { if (qaItems[key]) qaGrid.appendChild(qaItems[key]); });
            }

            allDashboardComponentIds.forEach(id => applyDashboardStatStyle(id, window.currentStyles[id]));
            updateStyleDisplays();
            showToast('Perubahan dibatalkan.', 'info');
            toggleEditMode();
        }

        function resetDashboardLayout() {
            if (!confirm('Kembalikan seluruh tampilan dashboard ke susunan default?')) return;
            fetch('{{ route("workspace.reset-layout") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ page: 'dashboard' })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const defaultOrder = data.layout_matrix?.dashboard_kpi_order || ['W-KPI-01', 'W-KPI-02', 'W-KPI-03', 'W-KPI-04'];
                    const defaultQaOrder = data.layout_matrix?.quick_actions_order || ['catalog', 'cart', 'orders', 'kitchen', 'delivery', 'finance', 'analytics', 'profile'];
                    
                    window.dashboardKpiLastSaved  = [...defaultOrder];
                    window.dashboardKpiCurrent    = [...defaultOrder];
                    window.quickActionsLastSaved   = [...defaultQaOrder];
                    window.quickActionsCurrent     = [...defaultQaOrder];

                    const resetStyles = (data.layout_matrix?.component_styles || {}).dashboard || window.dashboardDefaults;
                    window.currentStyles   = Object.assign({}, resetStyles);
                    window.lastSavedStyles = JSON.parse(JSON.stringify(window.currentStyles));

                    // Reorder KPI wrappers in DOM
                    const kpiGrid = document.getElementById('dashboard-kpi-grid');
                    if (kpiGrid) {
                        const wrappers = {};
                        kpiGrid.querySelectorAll('[data-kpi-wrapper]').forEach(w => { wrappers[w.dataset.kpiWrapper] = w; });
                        defaultOrder.forEach(key => { if (wrappers[key]) kpiGrid.appendChild(wrappers[key]); });
                    }

                    // Reorder QA in DOM
                    const qaGrid = document.getElementById('quick-actions-container');
                    if (qaGrid) {
                        const qaItems = {};
                        qaGrid.querySelectorAll('[data-action-key]').forEach(w => { qaItems[w.dataset.actionKey] = w; });
                        defaultQaOrder.forEach(key => { if (qaItems[key]) qaGrid.appendChild(qaItems[key]); });
                    }

                    allDashboardComponentIds.forEach(id => applyDashboardStatStyle(id, window.currentStyles[id]));
                    updateStyleDisplays();
                    showToast('✓ Tampilan berhasil dikembalikan ke default.', 'success');
                    if (window.isEditMode) toggleEditMode();
                }
            })
            .catch(() => showToast('Gagal melakukan reset layout.', 'error'));
        }

        function setDashboardStatShape(id, shape) {
            if (!window.currentStyles[id]) window.currentStyles[id] = {};
            window.currentStyles[id].shape = shape;
            applyDashboardStatStyle(id, window.currentStyles[id]);

            document.querySelectorAll('.d-shape-btn-' + id).forEach(btn => {
                const isActive = btn.dataset.shape === shape;
                btn.classList.toggle('bg-amber-500',   isActive);
                btn.classList.toggle('text-white',       isActive);
                btn.classList.toggle('border-amber-600', isActive);
                btn.classList.toggle('bg-white',        !isActive);
                btn.classList.toggle('text-slate-800',  !isActive);
                btn.classList.toggle('border-slate-300',!isActive);
            });
        }

        function onDashboardSizeInput(id, dim, value) {
            const v = parseInt(value);
            if (isNaN(v)) return;
            if (!window.currentStyles[id]) window.currentStyles[id] = {};
            window.currentStyles[id][dim] = v;
            applyDashboardStatStyle(id, window.currentStyles[id]);
        }

        function onDashboardSliderInput(id, dim, value) {
            const v = parseInt(value);
            if (isNaN(v)) return;
            if (!window.currentStyles[id]) window.currentStyles[id] = {};
            window.currentStyles[id][dim] = v;
            applyDashboardStatStyle(id, window.currentStyles[id]);
        }

        function applyDashboardStatStyle(id, style) {
            if (!style) return;
            const el = document.querySelector('[data-component-id="' + id + '"]');
            if (!el) return;

            const isKpi = id.startsWith('W-KPI-');
            const defaultW = isKpi ? 240 : (id.startsWith('W-QUICK') || id.startsWith('W-ORDERS') ? 900 : 440);
            const defaultH = isKpi ? 120 : (id === 'W-QUICK' ? 160 : (id === 'W-ORDERS' ? 320 : 200));

            const reqWidth  = parseInt(style.width)  || defaultW;
            const reqHeight = parseInt(style.height) || defaultH;
            const shape     = style.shape || 'rectangle';

            const bounds = isKpi
                ? { minW: 140, maxW: 400, minH: 80,  maxH: 300 }
                : { minW: 200, maxW: 900, minH: 120, maxH: 600 };

            const clampedW = Math.max(bounds.minW, Math.min(bounds.maxW, reqWidth));
            const clampedH = Math.max(bounds.minH, Math.min(bounds.maxH, reqHeight));

            el.style.maxWidth   = '';
            el.style.maxHeight  = '';
            el.style.minWidth   = '';
            el.style.minHeight  = '';
            el.style.boxSizing  = 'border-box';
            el.style.clipPath   = '';
            el.style.aspectRatio= '';
            el.style.borderRadius = '';
            el.style.alignSelf  = '';

            if (shape === 'circle') {
                const circleDiameter = Math.min(clampedW, clampedH);
                el.style.width        = `${circleDiameter}px`;
                el.style.height       = `${circleDiameter}px`;
                el.style.minWidth     = `${circleDiameter}px`;
                el.style.maxWidth     = `${circleDiameter}px`;
                el.style.minHeight    = `${circleDiameter}px`;
                el.style.maxHeight    = `${circleDiameter}px`;
                el.style.aspectRatio  = '1 / 1';
                el.style.borderRadius = '50%';
                el.style.margin       = '0 auto';
                el.style.alignSelf    = 'center';
            } else {
                el.style.width     = `${clampedW}px`;
                el.style.minHeight = `${clampedH}px`;
                el.style.height    = `${clampedH}px`;
                el.style.maxWidth  = '100%';
                el.style.margin    = '';

                switch (shape) {
                    case 'rounded':
                        el.style.borderRadius = '24px';
                        break;
                    case 'pill':
                        el.style.borderRadius = '9999px';
                        break;
                    case 'sharp':
                        el.style.borderRadius = '0px';
                        break;
                    case 'hexagon':
                        el.style.clipPath = 'polygon(25% 0%, 75% 0%, 100% 50%, 75% 100%, 25% 100%, 0% 50%)';
                        break;
                    default: // rectangle
                        const radius = parseInt(style.border_radius) || 16;
                        el.style.borderRadius = `${radius}px`;
                }
            }

            // TRUE ADAPTIVE CONTENT ENGINE
            if (window.adaptComponentContent) {
                window.adaptComponentContent(el, shape, clampedW, clampedH);
            }

            const actualRect = el.getBoundingClientRect();
            const actualW    = Math.round(actualRect.width);
            const actualH    = Math.round(actualRect.height);

            const display = document.getElementById('d-size-display-' + id);
            if (display) display.textContent = `${actualW} × ${actualH} px (Req: ${reqWidth}×${reqHeight})`;

            // Inputs sync
            const wInput = document.getElementById('d-width-' + id);
            const hInput = document.getElementById('d-height-' + id);
            const wVal   = document.getElementById('d-wval-'   + id);
            const hVal   = document.getElementById('d-hval-'   + id);

            if (wInput) wInput.value = reqWidth;
            if (hInput) hInput.value = reqHeight;
            if (wVal)   wVal.textContent = `${reqWidth}px`;
            if (hVal)   hVal.textContent = `${reqHeight}px`;
        }

        function updateStyleDisplays() {
            allDashboardComponentIds.forEach(id => {
                const style = window.currentStyles[id];
                if (!style) return;
                const isKpi = id.startsWith('W-KPI-');
                const defaultW = isKpi ? 240 : (id.startsWith('W-QUICK') || id.startsWith('W-ORDERS') ? 900 : 440);
                const defaultH = isKpi ? 120 : (id === 'W-QUICK' ? 160 : (id === 'W-ORDERS' ? 320 : 200));

                const w = style.width  || defaultW;
                const h = style.height || defaultH;

                const wInput = document.getElementById('d-width-' + id);
                const hInput = document.getElementById('d-height-' + id);
                const wVal   = document.getElementById('d-wval-'   + id);
                const hVal   = document.getElementById('d-hval-'   + id);

                if (wInput) wInput.value = w;
                if (hInput) hInput.value = h;
                if (wVal)   wVal.textContent = `${w}px`;
                if (hVal)   hVal.textContent = `${h}px`;

                if (style.shape) setDashboardStatShape(id, style.shape);
            });
        }

        function resetSingleDashboardWidget(id) {
            const defaults = window.dashboardDefaults[id] || { shape: 'rectangle', width: 240, height: 120, border_radius: 16 };
            window.currentStyles[id] = Object.assign({}, defaults);
            applyDashboardStatStyle(id, window.currentStyles[id]);

            // If it's a KPI card, also reposition it to its natural default relative index
            const defaultOrder = ['W-KPI-01', 'W-KPI-02', 'W-KPI-03', 'W-KPI-04'];
            const targetDefaultIdx = defaultOrder.indexOf(id);
            if (targetDefaultIdx !== -1) {
                const currentIdx = window.dashboardKpiCurrent.indexOf(id);
                if (currentIdx !== -1) {
                    window.dashboardKpiCurrent.splice(currentIdx, 1);
                    let insertIdx = window.dashboardKpiCurrent.length;
                    for (let i = 0; i < window.dashboardKpiCurrent.length; i++) {
                        const itemKey = window.dashboardKpiCurrent[i];
                        if (defaultOrder.indexOf(itemKey) > targetDefaultIdx) {
                            insertIdx = i;
                            break;
                        }
                    }
                    window.dashboardKpiCurrent.splice(insertIdx, 0, id);

                    const kpiGrid = document.getElementById('dashboard-kpi-grid');
                    if (kpiGrid) {
                        const wrappers = {};
                        kpiGrid.querySelectorAll('[data-kpi-wrapper]').forEach(w => { wrappers[w.dataset.kpiWrapper] = w; });
                        window.dashboardKpiCurrent.forEach(key => { if (wrappers[key]) kpiGrid.appendChild(wrappers[key]); });
                    }
                }
            }

            updateStyleDisplays();
            showToast('✓ Komponen ' + id + ' dikembalikan ke default.');
        }

        function showToast(msg, type = 'success') {
            const toast = document.getElementById('toast-notification');
            const msgEl = document.getElementById('toast-message');
            const iconEl = document.getElementById('toast-icon');

            msgEl.textContent = msg;
            iconEl.textContent = type === 'success' ? '✓' : (type === 'error' ? '✕' : 'ℹ');
            toast.classList.remove('hidden', 'translate-y-4', 'opacity-0');

            setTimeout(() => {
                toast.classList.add('translate-y-4', 'opacity-0');
                setTimeout(() => toast.classList.add('hidden'), 300);
            }, 3000);
        }
    </script>
</x-dashboard-layout>
