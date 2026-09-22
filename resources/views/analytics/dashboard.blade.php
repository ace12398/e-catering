<x-dashboard-layout>
    <x-slot name="title">Analisis Bisnis & Laporan — E-Catering</x-slot>
    <x-slot name="toolbarTitle">Analisis Bisnis (Business Intelligence)</x-slot>

    {{-- Chart.js & SortableJS --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

    @php
        $effectiveMatrix = $layout_matrix ?? auth()->user()?->preference?->effective_layout_matrix ?? \App\Models\WorkspacePreference::getDefaultLayoutMatrix();
        $analyticsSavedStyles = $analyticsStyles ?? $effectiveMatrix['component_styles']['analytics'] ?? [];
        $defaultAnalyticsStyles = \App\Models\WorkspacePreference::getDefaultLayoutMatrix()['component_styles']['analytics'];
        $analyticsKpiOrder = $analyticsKpiOrder ?? $effectiveMatrix['analytics_kpi_order'] ?? ['A-KPI-01', 'A-KPI-02', 'A-KPI-03', 'A-KPI-04', 'A-KPI-05', 'A-KPI-06'];
        $analyticsPanelsOrder = $analyticsPanelsOrder ?? $effectiveMatrix['analytics_panels_order'] ?? ['A-CHART-ORDERS', 'A-CHART-REVENUE', 'A-TOP-MENU', 'A-ORDER-DISTRIBUTION', 'A-ACTIVITY', 'A-SYSTEM'];

        $allKpiData = [
            'A-KPI-01' => [
                'id' => 'A-KPI-01',
                'label' => 'Total Pesanan',
                'icon' => '📦',
                'value' => number_format($summary['total_orders'] ?? 0),
                'subtext' => '↑ +14% bulan ini',
                'subtextColor' => 'emerald',
            ],
            'A-KPI-02' => [
                'id' => 'A-KPI-02',
                'label' => 'Total Pendapatan',
                'icon' => '💰',
                'value' => format_idr($summary['total_revenue'] ?? 0),
                'subtext' => '↑ Verified Paid',
                'subtextColor' => 'emerald',
            ],
            'A-KPI-03' => [
                'id' => 'A-KPI-03',
                'label' => 'Pesanan Hari Ini',
                'icon' => '📅',
                'value' => (string)($summary['today_orders'] ?? 0),
                'subtext' => 'Sedang diproses',
                'subtextColor' => 'blue',
            ],
            'A-KPI-04' => [
                'id' => 'A-KPI-04',
                'label' => 'Customer Aktif',
                'icon' => '👥',
                'value' => (string)($summary['active_customers'] ?? 0),
                'subtext' => 'Akun terverifikasi',
                'subtextColor' => 'purple',
            ],
            'A-KPI-05' => [
                'id' => 'A-KPI-05',
                'label' => 'Menu Terlaris',
                'icon' => '🔥',
                'value' => $summary['top_menu_name'] ?? 'Paket Menu',
                'subtext' => ($summary['top_menu_qty'] ?? 0) . ' Porsi Terjual',
                'subtextColor' => 'amber',
            ],
            'A-KPI-06' => [
                'id' => 'A-KPI-06',
                'label' => 'Rata-Rata Nilai',
                'icon' => '📊',
                'value' => format_idr($summary['avg_order_value'] ?? 0),
                'subtext' => 'Per transaksi order',
                'subtextColor' => 'slate',
            ],
        ];

        // Susun urutan KPI sesuai preference
        $sortedKpiList = [];
        foreach ($analyticsKpiOrder as $kId) {
            if (isset($allKpiData[$kId])) $sortedKpiList[] = $allKpiData[$kId];
        }
        foreach ($allKpiData as $kId => $kData) {
            if (!in_array($kId, $analyticsKpiOrder)) $sortedKpiList[] = $kData;
        }

        // Definisi Panels
        $allPanels = ['A-CHART-ORDERS', 'A-CHART-REVENUE', 'A-TOP-MENU', 'A-ORDER-DISTRIBUTION', 'A-ACTIVITY', 'A-SYSTEM'];
        $sortedPanelsList = [];
        foreach ($analyticsPanelsOrder as $pId) {
            if (in_array($pId, $allPanels)) $sortedPanelsList[] = $pId;
        }
        foreach ($allPanels as $pId) {
            if (!in_array($pId, $analyticsPanelsOrder)) $sortedPanelsList[] = $pId;
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

    <div class="max-w-7xl mx-auto space-y-6 relative" id="caterflow-analytics-root">

        {{-- TOAST NOTIFICATION --}}
        <div id="analytics-toast" class="hidden fixed bottom-6 right-6 z-50 p-4 rounded-xl bg-slate-900 text-white shadow-2xl border border-slate-700 flex items-center space-x-3 transition-all transform translate-y-4 opacity-0">
            <span id="analytics-toast-icon" class="text-amber-400 text-lg font-bold">✓</span>
            <span id="analytics-toast-message" class="text-xs font-medium">Layout analitik berhasil diperbarui.</span>
        </div>

        {{-- CANONICAL ADAPTIVE MODE BANNER (MATCHING /finance & /dashboard) --}}
        <div id="analytics-edit-mode-bar" class="hidden sticky top-4 z-40 p-4 rounded-2xl bg-amber-950/90 dark:bg-amber-950/95 backdrop-blur border-2 border-amber-500 text-white shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center space-x-3">
                <span class="p-2.5 rounded-xl bg-amber-500/20 border border-amber-400/30 text-amber-300 font-mono text-base">✨</span>
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-300">Mode Penyesuaian Tampilan Aktif — Analisis Bisnis</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500 text-slate-950">Drag & Drop Aktif</span>
                    </div>
                    <p class="text-xs text-amber-100/90 mt-0.5">Ubah bentuk (shape), lebar, tinggi, dan geser kartu KPI serta panel grafik analitik secara live.</p>
                </div>
            </div>
            <div class="flex items-center space-x-2 shrink-0">
                <button type="button" onclick="cancelAnalyticsLayout()" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs transition-colors border border-slate-600">
                    ✕ Batal
                </button>
                <button type="button" onclick="resetAnalyticsLayout()" class="px-3.5 py-2 rounded-xl bg-amber-900/80 hover:bg-amber-800 text-amber-200 font-semibold text-xs transition-colors border border-amber-600">
                    🔄 Reset Default
                </button>
                <button type="button" onclick="saveAnalyticsLayout()" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold text-xs shadow-md transition-colors flex items-center space-x-1.5">
                    <span>💾 Simpan Tampilan</span>
                </button>
            </div>
        </div>

        {{-- PAGE HEADER --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-6 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Laporan & Statistik</span>
                <h1 class="text-xl md:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight mt-0.5">Analisis Bisnis</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Ringkasan statistik performa operasional, grafik pendapatan, dan metrik bisnis katering.</p>
            </div>
            <div class="flex items-center space-x-2 shrink-0">
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    📊 Data Analitik Aktif
                </span>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                    {{ now()->format('d M Y') }}
                </span>
                <button type="button" id="analytics-btn-edit" onclick="toggleAnalyticsEditMode()"
                        class="h-9 px-4 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs transition-colors flex items-center space-x-2 shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <span id="analytics-btn-text">⚙️ Sesuaikan Tampilan</span>
                </button>
            </div>
        </div>

        {{-- 1. SECTION: 6 KPI CARDS (SORTABLE & RESIZABLE) --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 items-start" id="analytics-kpi-grid">
            @foreach($sortedKpiList as $kpi)
                @php $kpiId = $kpi['id']; @endphp
                <div data-kpi-wrapper="{{ $kpiId }}" class="w-full flex flex-col items-center space-y-3 transition-all">
                    
                    {{-- BUSINESS PREVIEW CARD FOR KPI --}}
                    <div id="analytics-card-{{ $kpiId }}" data-component-id="{{ $kpiId }}"
                         class="caterflow-visual-card w-full p-4 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm space-y-1 transition-all relative overflow-hidden flex flex-col justify-between">
                        
                        {{-- Dedicated Drag Handle Badge (Edit Mode Only) --}}
                        <div class="analytics-kpi-drag-handle hidden absolute top-2 right-2 z-10 px-2 py-0.5 rounded-md bg-amber-500/20 hover:bg-amber-500 text-amber-800 dark:text-amber-200 hover:text-white border border-amber-400/40 cursor-grab active:cursor-grabbing transition-colors flex items-center space-x-1 select-none shadow-sm"
                             title="Tahan dan geser untuk memindahkan urutan kartu KPI">
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
                            <div class="text-lg font-extrabold text-slate-900 dark:text-white stat-value mt-1 truncate" title="{{ $kpi['value'] }}">
                                {{ $kpi['value'] }}
                            </div>
                            <span class="text-[10px] text-{{ $kpi['subtextColor'] }}-600 font-semibold subtext block mt-0.5 truncate">
                                {{ $kpi['subtext'] }}
                            </span>
                        </div>
                    </div>

                    {{-- SEPARATE RECTANGULAR CONFIGURATION EDITOR (OUTSIDE PREVIEW) --}}
                    <div class="analytics-edit-controls hidden w-full p-3.5 rounded-xl bg-slate-900/90 text-white border border-slate-700/60 shadow-lg space-y-2.5 text-xs backdrop-blur z-20">
                        <div class="flex items-center justify-between font-bold border-b border-slate-700 pb-1.5 text-[10px]">
                            <div class="flex items-center space-x-1.5">
                                <span class="analytics-kpi-drag-handle cursor-grab active:cursor-grabbing px-2 py-0.5 rounded bg-amber-600 hover:bg-amber-500 text-white text-[10px] font-bold flex items-center space-x-1 shadow-sm transition-colors select-none"
                                      title="Tahan dan geser untuk memindahkan urutan">
                                    <span>⠿</span>
                                    <span>Geser</span>
                                </span>
                                <span class="text-amber-400 font-bold">Atur: {{ $kpi['label'] }}</span>
                            </div>
                            <button type="button" onclick="resetSingleAnalyticsComponent('{{ $kpiId }}')" class="px-1.5 py-0.5 rounded text-[9px] font-semibold bg-amber-800/80 hover:bg-amber-700 text-white border border-amber-600 transition-colors" title="Reset komponen ini">🔄 Reset</button>
                        </div>
                        <div class="space-y-1">
                            <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Bentuk</span>
                            <div class="grid grid-cols-3 gap-1">
                                @foreach(['rectangle'=>'■ Kotak','rounded'=>'◉ Bulat','sharp'=>'▪ Tajam','pill'=>'⬭ Pil','circle'=>'○ Lingkar','hexagon'=>'⬡ Hex'] as $sId => $sLabel)
                                    <button type="button" onclick="setAnalyticsComponentShape('{{ $kpiId }}', '{{ $sId }}')" class="a-shape-btn-{{ $kpiId }} px-1 py-1 rounded text-[9px] font-semibold border text-center transition-colors bg-white text-slate-800 border-slate-300 hover:bg-amber-500 hover:text-white" data-shape="{{ $sId }}">{{ $sLabel }}</button>
                                @endforeach
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-1.5">
                            <div>
                                <div class="flex items-center justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                                    <span>Lebar</span>
                                    <span id="a-wval-{{ $kpiId }}" class="text-amber-400 font-mono font-bold">240px</span>
                                </div>
                                <input type="range" min="140" max="400" step="10" value="240" id="a-width-slider-{{ $kpiId }}" oninput="onAnalyticsSliderInput('{{ $kpiId }}', 'width', this.value)" class="w-full accent-amber-500">
                            </div>
                            <div>
                                <div class="flex items-center justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                                    <span>Tinggi</span>
                                    <span id="a-hval-{{ $kpiId }}" class="text-amber-400 font-mono font-bold">120px</span>
                                </div>
                                <input type="range" min="80" max="240" step="10" value="120" id="a-height-slider-{{ $kpiId }}" oninput="onAnalyticsSliderInput('{{ $kpiId }}', 'height', this.value)" class="w-full accent-amber-500">
                            </div>
                        </div>
                        <div class="text-[9px] font-mono text-amber-400 pt-1 border-t border-slate-700/60" id="a-size-display-{{ $kpiId }}">240 × 120 px</div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- 2. SECTION: MAIN ANALYTICS PANELS & CHARTS (SORTABLE & RESIZABLE) --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start" id="analytics-panels-container">
            @foreach($sortedPanelsList as $panelId)
                @php
                    $isTopMenu = ($panelId === 'A-TOP-MENU');
                    $panelTitles = [
                        'A-CHART-ORDERS'       => ['title' => 'Grafik Pesanan (7 Hari)', 'badge' => 'Puncak: Jumat', 'badgeColor' => 'emerald'],
                        'A-CHART-REVENUE'      => ['title' => 'Grafik Pendapatan (Bulanan)', 'badge' => 'Tren Positif 📈', 'badgeColor' => 'emerald'],
                        'A-TOP-MENU'           => ['title' => 'Top 5 Menu Terlaris', 'badge' => 'Porsi Terjual', 'badgeColor' => 'amber'],
                        'A-ORDER-DISTRIBUTION' => ['title' => 'Distribusi Status Pesanan', 'badge' => 'Status Order', 'badgeColor' => 'blue'],
                        'A-ACTIVITY'           => ['title' => 'Aktivitas Sistem Terbaru', 'badge' => 'Live Feed', 'badgeColor' => 'purple'],
                        'A-SYSTEM'             => ['title' => 'Ringkasan Master Sistem', 'badge' => 'Data Sistem', 'badgeColor' => 'indigo'],
                    ];
                    $pInfo = $panelTitles[$panelId] ?? ['title' => $panelId, 'badge' => 'Panel', 'badgeColor' => 'slate'];
                @endphp

                <div data-panel-wrapper="{{ $panelId }}" class="w-full flex flex-col items-center space-y-4 transition-all {{ $isTopMenu ? 'lg:col-span-2' : '' }}">
                    
                    {{-- BUSINESS PREVIEW PANEL --}}
                    <div id="analytics-card-{{ $panelId }}" data-component-id="{{ $panelId }}"
                         class="caterflow-visual-card w-full p-6 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4 transition-all relative overflow-hidden">
                        
                        {{-- Dedicated Drag Handle Badge (Edit Mode Only) --}}
                        <div class="analytics-panel-drag-handle hidden absolute top-3 right-3 z-10 px-2 py-0.5 rounded-md bg-amber-500/20 hover:bg-amber-500 text-amber-800 dark:text-amber-200 hover:text-white border border-amber-400/40 cursor-grab active:cursor-grabbing transition-colors flex items-center space-x-1 select-none shadow-sm"
                             title="Tahan dan geser untuk memindahkan urutan panel">
                            <svg class="w-3 h-3 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 8h16M4 16h16"></path>
                            </svg>
                            <span class="text-[9px] font-extrabold font-mono pointer-events-none">GESER</span>
                        </div>

                        {{-- Panel Header --}}
                        <div class="flex items-center justify-between flex-between-header">
                            <div>
                                <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider adaptive-label">{{ $pInfo['title'] }}</h3>
                                <p class="text-[11px] text-slate-400 subtext">Komponen analitik bisnis CaterFlow</p>
                            </div>
                            <span class="text-xs font-bold text-{{ $pInfo['badgeColor'] }}-600 bg-{{ $pInfo['badgeColor'] }}-50 dark:bg-{{ $pInfo['badgeColor'] }}-950/40 px-2.5 py-1 rounded-lg subtext">{{ $pInfo['badge'] }}</span>
                        </div>

                        {{-- Panel Specific Content --}}
                        @if($panelId === 'A-CHART-ORDERS')
                            <div class="h-64 relative flex items-center justify-center">
                                <canvas id="weeklyOrdersChart"></canvas>
                            </div>
                        @elseif($panelId === 'A-CHART-REVENUE')
                            <div class="h-64 relative flex items-center justify-center">
                                <canvas id="monthlyRevenueChart"></canvas>
                            </div>
                        @elseif($panelId === 'A-TOP-MENU')
                            <div class="space-y-3.5">
                                @foreach($topMenus as $index => $item)
                                <div class="space-y-1.5">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-bold text-slate-800 dark:text-slate-200">
                                            <span class="text-emerald-600 mr-1.5">#{{ $index + 1 }}</span> {{ $item['name'] }}
                                        </span>
                                        <span class="font-extrabold text-slate-900 dark:text-white stat-value">{{ $item['count'] }} Porsi</span>
                                    </div>
                                    <div class="w-full h-2.5 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                                        <div class="h-full bg-emerald-600 rounded-full transition-all duration-700" style="width: {{ $item['percentage'] }}%"></div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @elseif($panelId === 'A-ORDER-DISTRIBUTION')
                            <div class="h-48 relative flex items-center justify-center">
                                <canvas id="orderStatusChart"></canvas>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-[11px] pt-2 border-t border-slate-100 dark:border-slate-800 subtext">
                                <div class="flex items-center space-x-2"><span class="w-3 h-3 rounded-full bg-amber-500 shrink-0"></span><span class="text-slate-600 dark:text-slate-400">Menunggu: 12</span></div>
                                <div class="flex items-center space-x-2"><span class="w-3 h-3 rounded-full bg-blue-500 shrink-0"></span><span class="text-slate-600 dark:text-slate-400">Diproses: 31</span></div>
                                <div class="flex items-center space-x-2"><span class="w-3 h-3 rounded-full bg-purple-500 shrink-0"></span><span class="text-slate-600 dark:text-slate-400">Dikirim: 18</span></div>
                                <div class="flex items-center space-x-2"><span class="w-3 h-3 rounded-full bg-emerald-500 shrink-0"></span><span class="text-slate-600 dark:text-slate-400">Selesai: 64</span></div>
                            </div>
                        @elseif($panelId === 'A-ACTIVITY')
                            <div class="space-y-3">
                                @foreach($recentActivities as $act)
                                <div class="flex items-start space-x-3.5 p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs">
                                    <span class="text-xl shrink-0 adaptive-icon">{{ $act['icon'] }}</span>
                                    <div class="flex-1 space-y-0.5">
                                        <div class="flex items-center justify-between flex-between-header">
                                            <span class="font-bold text-slate-900 dark:text-white">{{ $act['title'] }}</span>
                                            <span class="text-[10px] text-slate-400 font-semibold subtext">{{ $act['time'] }}</span>
                                        </div>
                                        <p class="text-[11px] text-slate-600 dark:text-slate-400 subtext">{{ $act['desc'] }}</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @elseif($panelId === 'A-SYSTEM')
                            <div class="space-y-3 text-xs">
                                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex-between-header">
                                    <span class="text-slate-600 dark:text-slate-400 font-semibold">Jumlah Menu Katering</span>
                                    <span class="font-extrabold text-emerald-600 dark:text-emerald-400 stat-value">{{ $systemTotals['menu_count'] ?? 0 }} Paket</span>
                                </div>
                                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex-between-header">
                                    <span class="text-slate-600 dark:text-slate-400 font-semibold">Jumlah Customer Terdaftar</span>
                                    <span class="font-extrabold text-purple-600 dark:text-purple-400 stat-value">{{ $systemTotals['customer_count'] ?? 0 }} Akun</span>
                                </div>
                                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex-between-header">
                                    <span class="text-slate-600 dark:text-slate-400 font-semibold">Jumlah Admin Pengelola</span>
                                    <span class="font-extrabold text-slate-900 dark:text-white stat-value">{{ $systemTotals['admin_count'] ?? 0 }} Personil</span>
                                </div>
                                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex-between-header">
                                    <span class="text-slate-600 dark:text-slate-400 font-semibold">Jumlah Kurir Pengiriman</span>
                                    <span class="font-extrabold text-blue-600 dark:text-blue-400 stat-value">{{ $systemTotals['courier_count'] ?? 0 }} Armada</span>
                                </div>
                                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex-between-header">
                                    <span class="text-slate-600 dark:text-slate-400 font-semibold">Jumlah Invoice Terbit</span>
                                    <span class="font-extrabold text-amber-600 dark:text-amber-400 stat-value">{{ $systemTotals['invoice_count'] ?? 0 }} Invoice</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- SEPARATE RECTANGULAR CONFIGURATION EDITOR (OUTSIDE PREVIEW) --}}
                    <div class="analytics-edit-controls hidden w-full p-3.5 rounded-xl bg-slate-900/90 text-white border border-slate-700/60 shadow-lg space-y-2.5 text-xs backdrop-blur z-20">
                        <div class="flex items-center justify-between font-bold border-b border-slate-700 pb-1.5 text-[10px]">
                            <div class="flex items-center space-x-1.5">
                                <span class="analytics-panel-drag-handle cursor-grab active:cursor-grabbing px-2 py-0.5 rounded bg-amber-600 hover:bg-amber-500 text-white text-[10px] font-bold flex items-center space-x-1 shadow-sm transition-colors select-none"
                                      title="Tahan dan geser untuk memindahkan urutan">
                                    <span>⠿</span>
                                    <span>Geser</span>
                                </span>
                                <span class="text-amber-400 font-bold">Atur: {{ $pInfo['title'] }}</span>
                            </div>
                            <button type="button" onclick="resetSingleAnalyticsComponent('{{ $panelId }}')" class="px-1.5 py-0.5 rounded text-[9px] font-semibold bg-amber-800/80 hover:bg-amber-700 text-white border border-amber-600 transition-colors" title="Reset komponen ini">🔄 Reset</button>
                        </div>
                        <div class="space-y-1">
                            <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Bentuk</span>
                            <div class="grid grid-cols-3 sm:grid-cols-6 gap-1">
                                @foreach(['rectangle'=>'■ Kotak','rounded'=>'◉ Bulat','sharp'=>'▪ Tajam','pill'=>'⬭ Pil','circle'=>'○ Lingkar','hexagon'=>'⬡ Hex'] as $sId => $sLabel)
                                    <button type="button" onclick="setAnalyticsComponentShape('{{ $panelId }}', '{{ $sId }}')" class="a-shape-btn-{{ $panelId }} px-1 py-1 rounded text-[9px] font-semibold border text-center transition-colors bg-white text-slate-800 border-slate-300 hover:bg-amber-500 hover:text-white" data-shape="{{ $sId }}">{{ $sLabel }}</button>
                                @endforeach
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <div class="flex items-center justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                                    <span>Lebar</span>
                                    <span id="a-wval-{{ $panelId }}" class="text-amber-400 font-mono font-bold">440px</span>
                                </div>
                                <input type="range" min="280" max="900" step="10" value="440" id="a-width-slider-{{ $panelId }}" oninput="onAnalyticsSliderInput('{{ $panelId }}', 'width', this.value)" class="w-full accent-amber-500">
                            </div>
                            <div>
                                <div class="flex items-center justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                                    <span>Tinggi</span>
                                    <span id="a-hval-{{ $panelId }}" class="text-amber-400 font-mono font-bold">340px</span>
                                </div>
                                <input type="range" min="180" max="600" step="10" value="340" id="a-height-slider-{{ $panelId }}" oninput="onAnalyticsSliderInput('{{ $panelId }}', 'height', this.value)" class="w-full accent-amber-500">
                            </div>
                        </div>
                        <div class="text-[9px] font-mono text-amber-400 pt-1 border-t border-slate-700/60" id="a-size-display-{{ $panelId }}">440 × 340 px</div>
                    </div>
                </div>
            @endforeach
        </div>

    </div>

    {{-- INITIALIZE CHART.JS & ANALYTICS ADAPTIVE ENGINE (MATCHING /finance & /dashboard) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initAnalyticsCharts();

            window.analyticsIsEditMode       = false;
            window.analyticsKpiOrder         = @json($analyticsKpiOrder);
            window.analyticsKpiLastSaved     = [...window.analyticsKpiOrder];
            window.analyticsPanelsOrder      = @json($analyticsPanelsOrder);
            window.analyticsPanelsLastSaved  = [...window.analyticsPanelsOrder];
            window.analyticsSavedStyles      = @json($analyticsSavedStyles);
            window.analyticsDefaults         = @json($defaultAnalyticsStyles);
            window.analyticsCurrentStyles    = Object.assign({}, window.analyticsDefaults, window.analyticsSavedStyles);
            window.analyticsLastSavedStyles  = JSON.parse(JSON.stringify(window.analyticsCurrentStyles));
            window.analyticsKpiSortable      = null;
            window.analyticsPanelsSortable   = null;

            initAnalyticsSortables();
            initAnalyticsComponentStyles();
        });

        function initAnalyticsCharts() {
            if (typeof Chart === 'undefined') return;

            // 1. Weekly Orders Chart
            const weeklyCtx = document.getElementById('weeklyOrdersChart')?.getContext('2d');
            if (weeklyCtx) {
                new Chart(weeklyCtx, {
                    type: 'line',
                    data: {
                        labels: @json($weeklyOrders['labels']),
                        datasets: [{
                            label: 'Jumlah Pesanan',
                            data: @json($weeklyOrders['data']),
                            borderColor: '#10B981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            borderWidth: 2.5,
                            fill: true,
                            tension: 0.35,
                            pointRadius: 4,
                            pointBackgroundColor: '#10B981'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, grid: { borderDash: [2, 4] } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            // 2. Monthly Revenue Chart
            const monthlyCtx = document.getElementById('monthlyRevenueChart')?.getContext('2d');
            if (monthlyCtx) {
                new Chart(monthlyCtx, {
                    type: 'bar',
                    data: {
                        labels: @json($monthlyRevenue['labels']),
                        datasets: [{
                            label: 'Pendapatan (Juta Rp)',
                            data: @json($monthlyRevenue['data']),
                            backgroundColor: '#3B82F6',
                            borderRadius: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, grid: { borderDash: [2, 4] } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            // 3. Order Status Doughnut Chart
            const statusCtx = document.getElementById('orderStatusChart')?.getContext('2d');
            if (statusCtx) {
                new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: @json($orderStatuses['labels']),
                        datasets: [{
                            data: @json($orderStatuses['data']),
                            backgroundColor: @json($orderStatuses['colors']),
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        cutout: '70%'
                    }
                });
            }
        }

        function initAnalyticsSortables() {
            // 1. Sortable for Top KPI Cards
            const kpiGrid = document.getElementById('analytics-kpi-grid');
            if (kpiGrid) {
                window.analyticsKpiSortable = new Sortable(kpiGrid, {
                    animation: 200,
                    handle: '.analytics-kpi-drag-handle',
                    ghostClass: 'dashboard-sortable-ghost',
                    dragClass: 'dashboard-sortable-drag',
                    chosenClass: 'dashboard-sortable-chosen',
                    disabled: true,
                    onEnd: function() {
                        const newOrder = [];
                        kpiGrid.querySelectorAll('[data-kpi-wrapper]').forEach(el => newOrder.push(el.dataset.kpiWrapper));
                        window.analyticsKpiOrder = newOrder;
                    }
                });
            }

            // 2. Sortable for Analytics Main Panels
            const panelsContainer = document.getElementById('analytics-panels-container');
            if (panelsContainer) {
                window.analyticsPanelsSortable = new Sortable(panelsContainer, {
                    animation: 200,
                    handle: '.analytics-panel-drag-handle',
                    ghostClass: 'dashboard-sortable-ghost',
                    dragClass: 'dashboard-sortable-drag',
                    chosenClass: 'dashboard-sortable-chosen',
                    disabled: true,
                    onEnd: function() {
                        const newOrder = [];
                        panelsContainer.querySelectorAll('[data-panel-wrapper]').forEach(el => newOrder.push(el.dataset.panelWrapper));
                        window.analyticsPanelsOrder = newOrder;
                    }
                });
            }
        }

        const allAnalyticsComponentIds = [
            'A-KPI-01', 'A-KPI-02', 'A-KPI-03', 'A-KPI-04', 'A-KPI-05', 'A-KPI-06',
            'A-CHART-ORDERS', 'A-CHART-REVENUE', 'A-TOP-MENU', 'A-ORDER-DISTRIBUTION',
            'A-ACTIVITY', 'A-SYSTEM'
        ];

        function initAnalyticsComponentStyles() {
            allAnalyticsComponentIds.forEach(id => {
                const style = window.analyticsCurrentStyles[id];
                if (style) applyAnalyticsComponentStyle(id, style);
            });
        }

        function toggleAnalyticsEditMode() {
            window.analyticsIsEditMode = !window.analyticsIsEditMode;
            const bar  = document.getElementById('analytics-edit-mode-bar');
            const btn  = document.getElementById('analytics-btn-text');
            const root = document.getElementById('caterflow-analytics-root');

            if (window.analyticsIsEditMode) {
                bar.classList.remove('hidden');
                btn.textContent = '✕ Keluar Mode Penyesuaian';
                root.classList.add('in-edit-mode');
                document.querySelectorAll('.analytics-edit-controls').forEach(el => el.classList.remove('hidden'));
                document.querySelectorAll('.analytics-kpi-drag-handle').forEach(el => el.classList.remove('hidden'));
                document.querySelectorAll('.analytics-panel-drag-handle').forEach(el => el.classList.remove('hidden'));
                document.querySelectorAll('[data-kpi-wrapper], [data-panel-wrapper]').forEach(el => {
                    el.classList.add('p-1.5', 'rounded-2xl', 'border-2', 'border-dashed', 'border-amber-500/50', 'bg-amber-50/10');
                });
            } else {
                bar.classList.add('hidden');
                btn.textContent = '⚙️ Sesuaikan Tampilan';
                root.classList.remove('in-edit-mode');
                document.querySelectorAll('.analytics-edit-controls').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('.analytics-kpi-drag-handle').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('.analytics-panel-drag-handle').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('[data-kpi-wrapper], [data-panel-wrapper]').forEach(el => {
                    el.classList.remove('p-1.5', 'rounded-2xl', 'border-2', 'border-dashed', 'border-amber-500/50', 'bg-amber-50/10');
                });
            }

            if (window.analyticsKpiSortable) window.analyticsKpiSortable.option('disabled', !window.analyticsIsEditMode);
            if (window.analyticsPanelsSortable) window.analyticsPanelsSortable.option('disabled', !window.analyticsIsEditMode);
            if (window.analyticsIsEditMode) updateAnalyticsStyleDisplays();
        }

        function setAnalyticsComponentShape(id, shape) {
            if (!window.analyticsCurrentStyles[id]) window.analyticsCurrentStyles[id] = {};
            window.analyticsCurrentStyles[id].shape = shape;
            applyAnalyticsComponentStyle(id, window.analyticsCurrentStyles[id]);

            document.querySelectorAll('.a-shape-btn-' + id).forEach(btn => {
                const isActive = btn.dataset.shape === shape;
                btn.classList.toggle('bg-amber-500',   isActive);
                btn.classList.toggle('text-white',       isActive);
                btn.classList.toggle('border-amber-600', isActive);
                btn.classList.toggle('bg-white',        !isActive);
                btn.classList.toggle('text-slate-800',  !isActive);
                btn.classList.toggle('border-slate-300',!isActive);
            });
        }

        function onAnalyticsSliderInput(id, dim, value) {
            const v = parseInt(value);
            if (isNaN(v)) return;
            if (!window.analyticsCurrentStyles[id]) window.analyticsCurrentStyles[id] = {};
            window.analyticsCurrentStyles[id][dim] = v;
            applyAnalyticsComponentStyle(id, window.analyticsCurrentStyles[id]);
        }

        function applyAnalyticsComponentStyle(id, style) {
            if (!style) return;
            const visualCard = document.querySelector('[data-component-id="' + id + '"]');
            if (!visualCard) return;

            const isKpi = id.startsWith('A-KPI-');
            const defaultW = isKpi ? 240 : (id === 'A-TOP-MENU' ? 560 : 440);
            const defaultH = isKpi ? 120 : (id.startsWith('A-CHART-') ? 340 : (id === 'A-ORDER-DISTRIBUTION' ? 320 : 260));

            const reqWidth  = parseInt(style.width)  || defaultW;
            const reqHeight = parseInt(style.height) || defaultH;
            const shape     = style.shape || 'rectangle';

            const bounds = isKpi
                ? { minW: 140, maxW: 400, minH: 80,  maxH: 240 }
                : { minW: 280, maxW: 900, minH: 180, maxH: 600 };

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

            const display = document.getElementById('a-size-display-' + id);
            if (display) display.textContent = `${actualW} × ${actualH} px (Req: ${reqWidth}×${reqHeight})`;

            const wSlider = document.getElementById('a-width-slider-'  + id);
            const hSlider = document.getElementById('a-height-slider-' + id);
            const wVal    = document.getElementById('a-wval-'          + id);
            const hVal    = document.getElementById('a-hval-'          + id);

            if (wSlider) wSlider.value = reqWidth;
            if (hSlider) hSlider.value = reqHeight;
            if (wVal)    wVal.textContent = `${reqWidth}px`;
            if (hVal)    hVal.textContent = `${reqHeight}px`;

            // Chart.js responsive resize
            if (typeof Chart !== 'undefined') {
                if (id === 'A-CHART-ORDERS') {
                    const ch = Chart.getChart('weeklyOrdersChart');
                    if (ch) ch.resize();
                } else if (id === 'A-CHART-REVENUE') {
                    const ch = Chart.getChart('monthlyRevenueChart');
                    if (ch) ch.resize();
                } else if (id === 'A-ORDER-DISTRIBUTION') {
                    const ch = Chart.getChart('orderStatusChart');
                    if (ch) ch.resize();
                }
            }
        }

        function updateAnalyticsStyleDisplays() {
            allAnalyticsComponentIds.forEach(id => {
                const style = window.analyticsCurrentStyles[id];
                if (!style) return;
                const isKpi = id.startsWith('A-KPI-');
                const defaultW = isKpi ? 240 : (id === 'A-TOP-MENU' ? 560 : 440);
                const defaultH = isKpi ? 120 : (id.startsWith('A-CHART-') ? 340 : (id === 'A-ORDER-DISTRIBUTION' ? 320 : 260));

                const w = style.width  || defaultW;
                const h = style.height || defaultH;

                const wSlider = document.getElementById('a-width-slider-'  + id);
                const hSlider = document.getElementById('a-height-slider-' + id);
                const wVal    = document.getElementById('a-wval-'          + id);
                const hVal    = document.getElementById('a-hval-'          + id);

                if (wSlider) wSlider.value = w;
                if (hSlider) hSlider.value = h;
                if (wVal)    wVal.textContent = `${w}px`;
                if (hVal)    hVal.textContent = `${h}px`;

                if (style.shape) setAnalyticsComponentShape(id, style.shape);
            });
        }

        function saveAnalyticsLayout() {
            fetch('{{ route("workspace.save-layout") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ layout_matrix: {
                    analytics_kpi_order:    window.analyticsKpiOrder,
                    analytics_panels_order: window.analyticsPanelsOrder,
                    component_styles: { analytics: JSON.parse(JSON.stringify(window.analyticsCurrentStyles)) }
                }})
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    window.analyticsLastSavedStyles = JSON.parse(JSON.stringify(window.analyticsCurrentStyles));
                    window.analyticsKpiLastSaved    = [...window.analyticsKpiOrder];
                    window.analyticsPanelsLastSaved = [...window.analyticsPanelsOrder];
                    showAnalyticsToast('✓ Urutan & tampilan analitik berhasil disimpan.', 'success');
                    toggleAnalyticsEditMode();
                } else {
                    showAnalyticsToast('Gagal menyimpan: ' + (data.message || 'Error'), 'error');
                }
            })
            .catch(() => showAnalyticsToast('Kesalahan koneksi saat menyimpan layout.', 'error'));
        }

        function cancelAnalyticsLayout() {
            window.analyticsCurrentStyles = JSON.parse(JSON.stringify(window.analyticsLastSavedStyles));
            window.analyticsKpiOrder      = [...window.analyticsKpiLastSaved];
            window.analyticsPanelsOrder   = [...window.analyticsPanelsLastSaved];

            // Reorder KPI cards in DOM
            const kpiGrid = document.getElementById('analytics-kpi-grid');
            if (kpiGrid && window.analyticsKpiOrder.length) {
                const wrappers = {};
                kpiGrid.querySelectorAll('[data-kpi-wrapper]').forEach(w => { wrappers[w.dataset.kpiWrapper] = w; });
                window.analyticsKpiOrder.forEach(kId => { if (wrappers[kId]) kpiGrid.appendChild(wrappers[kId]); });
            }

            // Reorder Panels in DOM
            const panelsContainer = document.getElementById('analytics-panels-container');
            if (panelsContainer && window.analyticsPanelsOrder.length) {
                const wrappers = {};
                panelsContainer.querySelectorAll('[data-panel-wrapper]').forEach(w => { wrappers[w.dataset.panelWrapper] = w; });
                window.analyticsPanelsOrder.forEach(pId => { if (wrappers[pId]) panelsContainer.appendChild(wrappers[pId]); });
            }

            allAnalyticsComponentIds.forEach(id => applyAnalyticsComponentStyle(id, window.analyticsCurrentStyles[id]));
            showAnalyticsToast('Perubahan dibatalkan.', 'info');
            toggleAnalyticsEditMode();
        }

        function resetAnalyticsLayout() {
            if (!confirm('Kembalikan seluruh tampilan analitik ke susunan default?')) return;
            fetch('{{ route("workspace.reset-layout") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ page: 'analytics' })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const defaultKpiOrder    = data.layout_matrix?.analytics_kpi_order    || ['A-KPI-01', 'A-KPI-02', 'A-KPI-03', 'A-KPI-04', 'A-KPI-05', 'A-KPI-06'];
                    const defaultPanelsOrder  = data.layout_matrix?.analytics_panels_order || ['A-CHART-ORDERS', 'A-CHART-REVENUE', 'A-TOP-MENU', 'A-ORDER-DISTRIBUTION', 'A-ACTIVITY', 'A-SYSTEM'];
                    window.analyticsKpiOrder        = [...defaultKpiOrder];
                    window.analyticsKpiLastSaved    = [...defaultKpiOrder];
                    window.analyticsPanelsOrder     = [...defaultPanelsOrder];
                    window.analyticsPanelsLastSaved = [...defaultPanelsOrder];

                    const resetStyles = (data.layout_matrix?.component_styles || {}).analytics || window.analyticsDefaults;
                    window.analyticsCurrentStyles   = Object.assign({}, resetStyles);
                    window.analyticsLastSavedStyles = JSON.parse(JSON.stringify(window.analyticsCurrentStyles));

                    // Reorder KPI cards in DOM
                    const kpiGrid = document.getElementById('analytics-kpi-grid');
                    if (kpiGrid && window.analyticsKpiOrder.length) {
                        const wrappers = {};
                        kpiGrid.querySelectorAll('[data-kpi-wrapper]').forEach(w => { wrappers[w.dataset.kpiWrapper] = w; });
                        window.analyticsKpiOrder.forEach(kId => { if (wrappers[kId]) kpiGrid.appendChild(wrappers[kId]); });
                    }

                    // Reorder Panels in DOM
                    const panelsContainer = document.getElementById('analytics-panels-container');
                    if (panelsContainer && window.analyticsPanelsOrder.length) {
                        const wrappers = {};
                        panelsContainer.querySelectorAll('[data-panel-wrapper]').forEach(w => { wrappers[w.dataset.panelWrapper] = w; });
                        window.analyticsPanelsOrder.forEach(pId => { if (wrappers[pId]) panelsContainer.appendChild(wrappers[pId]); });
                    }

                    allAnalyticsComponentIds.forEach(id => applyAnalyticsComponentStyle(id, window.analyticsCurrentStyles[id]));
                    showAnalyticsToast('✓ Seluruh tampilan analitik dikembalikan ke default.', 'success');
                    if (window.analyticsIsEditMode) toggleAnalyticsEditMode();
                }
            })
            .catch(() => showAnalyticsToast('Gagal melakukan reset layout.', 'error'));
        }

        function resetSingleAnalyticsComponent(id) {
            const defaults = window.analyticsDefaults[id] || { shape: 'rectangle', width: 240, height: 120, border_radius: 16 };
            window.analyticsCurrentStyles[id] = Object.assign({}, defaults);
            applyAnalyticsComponentStyle(id, window.analyticsCurrentStyles[id]);
            updateAnalyticsStyleDisplays();
            showAnalyticsToast('✓ Komponen ' + id + ' dikembalikan ke default.', 'success');
        }

        function showAnalyticsToast(msg, type = 'success') {
            const toast = document.getElementById('analytics-toast');
            const msgEl = document.getElementById('analytics-toast-message');
            const icon  = document.getElementById('analytics-toast-icon');
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
