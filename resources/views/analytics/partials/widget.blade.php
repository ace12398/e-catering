{{-- ANALYTICS WIDGET CONTAINER — ADAPTABLE UI --}}
@php
    $wId      = $wId      ?? 'A-KPI';
    $slotName = $slotName ?? 'top';

    $widgetMeta = [
        'A-KPI'      => ['title' => 'Ringkasan KPI Bisnis', 'icon' => '📊'],
        'A-CHARTS'   => ['title' => 'Grafik Tren (Pesanan & Pendapatan)', 'icon' => '📈'],
        'A-TOP-MENU' => ['title' => 'Top 5 Menu Terlaris & Distribusi Status', 'icon' => '🔥'],
        'A-ACTIVITY' => ['title' => 'Aktivitas Sistem Terbaru', 'icon' => '⚡'],
        'A-SYSTEM'   => ['title' => 'Ringkasan Master Sistem', 'icon' => '🖥️'],
    ];
    $meta = $widgetMeta[$wId] ?? ['title' => 'Widget Analitik', 'icon' => '📌'];
@endphp

<div id="analytics-widget-{{ $wId }}" data-widget-id="{{ $wId }}" class="analytics-widget transition-all relative rounded-xl group">

    {{-- EDIT MODE DRAG HANDLE HEADER (tersembunyi di Normal Mode) --}}
    <div class="analytics-edit-controls hidden p-3 rounded-t-xl bg-slate-900 text-white flex flex-col gap-2 border-b border-slate-700">
        {{-- ROW 1: Drag handle + slot controls --}}
        <div class="flex items-center justify-between text-xs">
            <div class="flex items-center space-x-2">
                <span class="analytics-drag-handle cursor-grab active:cursor-grabbing px-2 py-1 bg-slate-800 hover:bg-slate-700 rounded font-mono font-bold text-emerald-400 select-none shadow-sm flex items-center space-x-1" title="Tarik untuk memindahkan widget">
                    <span>⋮⋮</span>
                    <span>Drag</span>
                </span>
                <span class="font-bold text-slate-200">{{ $meta['icon'] }} {{ $meta['title'] }}</span>
                <span class="px-2 py-0.5 rounded text-[10px] uppercase font-semibold bg-emerald-950 text-emerald-300 border border-emerald-800">
                    Slot: {{ strtoupper($slotName) }}
                </span>
            </div>
            <div class="flex items-center space-x-1.5 shrink-0">
                <button type="button" onclick="moveAnalyticsWidgetDirection('{{ $wId }}', 'up')" class="px-2 py-1 bg-slate-800 hover:bg-slate-700 rounded text-slate-300 hover:text-white font-bold" title="Pindah Atas">▲</button>
                <button type="button" onclick="moveAnalyticsWidgetDirection('{{ $wId }}', 'down')" class="px-2 py-1 bg-slate-800 hover:bg-slate-700 rounded text-slate-300 hover:text-white font-bold" title="Pindah Bawah">▼</button>
                <span class="text-slate-600">|</span>
                <button type="button" onclick="moveAnalyticsWidget('{{ $wId }}', 'top')" class="px-2 py-0.5 bg-slate-800 hover:bg-emerald-800 rounded text-[10px] font-semibold text-slate-300 hover:text-white" title="Pindah ke Slot Top">Top</button>
                <button type="button" onclick="moveAnalyticsWidget('{{ $wId }}', 'left')" class="px-2 py-0.5 bg-slate-800 hover:bg-emerald-800 rounded text-[10px] font-semibold text-slate-300 hover:text-white" title="Pindah ke Slot Left">Left</button>
                <button type="button" onclick="moveAnalyticsWidget('{{ $wId }}', 'right')" class="px-2 py-0.5 bg-slate-800 hover:bg-emerald-800 rounded text-[10px] font-semibold text-slate-300 hover:text-white" title="Pindah ke Slot Right">Right</button>
                <button type="button" onclick="moveAnalyticsWidget('{{ $wId }}', 'bottom')" class="px-2 py-0.5 bg-slate-800 hover:bg-emerald-800 rounded text-[10px] font-semibold text-slate-300 hover:text-white" title="Pindah ke Slot Bottom">Bottom</button>
            </div>
        </div>

        {{-- ROW 2: SHAPE & SIZE PANEL --}}
        <div class="analytics-style-panel bg-slate-800 rounded-lg p-3 space-y-3 text-xs border border-slate-700">
            {{-- Shape Picker --}}
            <div class="space-y-1.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Bentuk Komponen</span>
                <div class="flex flex-wrap gap-1.5">
                    @php
                        $aAllShapes = [
                            'rectangle' => ['label' => '■ Persegi Panjang', 'title' => 'Persegi panjang dengan sudut membulat'],
                            'rounded'   => ['label' => '◉ Membulat',        'title' => 'Sudut sangat membulat'],
                            'sharp'     => ['label' => '▪ Tajam',            'title' => 'Sudut tajam sempurna'],
                            'pill'      => ['label' => '⬭ Pil / Oval',      'title' => 'Bentuk pil memanjang'],
                            'circle'    => ['label' => '○ Lingkaran',        'title' => 'Lingkaran sempurna'],
                            'hexagon'   => ['label' => '⬡ Heksagon',        'title' => 'Bentuk segi enam'],
                        ];
                        $aAllowedShapes = \App\Models\WorkspacePreference::getCompatibleShapes($wId);
                    @endphp
                    @foreach($aAllShapes as $shapeId => $shapeMeta)
                        @if(in_array($shapeId, $aAllowedShapes, true))
                        <button type="button"
                            data-shape="{{ $shapeId }}"
                            onclick="setAnalyticsWidgetShape('{{ $wId }}', '{{ $shapeId }}')"
                            title="{{ $shapeMeta['title'] }}"
                            class="a-shape-btn-{{ $wId }} px-2.5 py-1.5 rounded-md text-[10px] font-semibold border transition-colors bg-slate-700 border-slate-600 text-slate-300 hover:bg-emerald-900 hover:border-emerald-600 hover:text-emerald-300"
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
                            <input type="number" id="a-width-input-{{ $wId }}" min="240" max="900" step="10" value="480"
                                oninput="onAnalyticsSizeInput('{{ $wId }}', 'width', this.value)"
                                class="w-16 px-1.5 py-1 rounded bg-slate-700 border border-slate-600 text-white text-[11px] text-right focus:border-emerald-500 focus:outline-none">
                            <span class="text-slate-500 text-[10px]">px</span>
                        </div>
                    </div>
                    <input type="range" id="a-width-slider-{{ $wId }}" min="240" max="900" step="10" value="480"
                        oninput="onAnalyticsSliderInput('{{ $wId }}', 'width', this.value)"
                        class="w-full h-1.5 accent-emerald-500 cursor-pointer">
                </div>
                {{-- Height --}}
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tinggi</span>
                        <div class="flex items-center space-x-1">
                            <input type="number" id="a-height-input-{{ $wId }}" min="120" max="600" step="10" value="360"
                                oninput="onAnalyticsSizeInput('{{ $wId }}', 'height', this.value)"
                                class="w-16 px-1.5 py-1 rounded bg-slate-700 border border-slate-600 text-white text-[11px] text-right focus:border-emerald-500 focus:outline-none">
                            <span class="text-slate-500 text-[10px]">px</span>
                        </div>
                    </div>
                    <input type="range" id="a-height-slider-{{ $wId }}" min="120" max="600" step="10" value="360"
                        oninput="onAnalyticsSliderInput('{{ $wId }}', 'height', this.value)"
                        class="w-full h-1.5 accent-emerald-500 cursor-pointer">
                </div>
            </div>

            {{-- Live Measurement Display & Per-Component Reset --}}
            <div class="flex items-center justify-between pt-1 border-t border-slate-700">
                <div class="flex items-center space-x-2">
                    <span class="text-[10px] text-slate-500">Ukuran aktual:</span>
                    <span id="a-size-display-{{ $wId }}" class="text-[10px] font-mono font-semibold text-emerald-400">480 × 360 px</span>
                </div>
                <button type="button" onclick="resetSingleAnalyticsWidget('{{ $wId }}')" class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-800/80 hover:bg-amber-700 text-white border border-amber-600 transition-colors" title="Reset komponen ini ke default">🔄 Reset</button>
            </div>
        </div>
    </div>

    {{-- INNER VISUAL COMPONENT CARD (#analytics-widget-card-{wId}) --}}
    <div id="analytics-widget-card-{{ $wId }}" class="caterflow-visual-card transition-all w-full h-full max-w-full overflow-hidden">



    {{-- ============================================================ --}}
    {{-- WIDGET CONTENT — IDENTIK DENGAN DESAIN ANALYTICS ASLI       --}}
    {{-- ============================================================ --}}

    @if($wId === 'A-KPI')
        {{-- 6 KPI SUMMARY CARDS (desain asli dipertahankan 100%) --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 analytics-content-box">
            <div class="p-4 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm space-y-1 kpi-card-box">
                <span class="text-[11px] font-semibold text-slate-500 uppercase block adaptive-label">Total Pesanan</span>
                <div class="text-xl font-extrabold text-slate-900 dark:text-white stat-value">{{ number_format($summary['total_orders']) }}</div>
                <span class="text-[10px] text-emerald-600 font-semibold subtext">↑ +14% bulan ini</span>
            </div>
            <div class="p-4 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm space-y-1 kpi-card-box">
                <span class="text-[11px] font-semibold text-slate-500 uppercase block adaptive-label">Total Pendapatan</span>
                <div class="text-lg font-extrabold text-emerald-600 dark:text-emerald-400 stat-value">{{ format_idr($summary['total_revenue']) }}</div>
                <span class="text-[10px] text-emerald-600 font-semibold subtext">↑ Verified Paid</span>
            </div>
            <div class="p-4 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm space-y-1 kpi-card-box">
                <span class="text-[11px] font-semibold text-slate-500 uppercase block adaptive-label">Pesanan Hari Ini</span>
                <div class="text-xl font-extrabold text-blue-600 dark:text-blue-400 stat-value">{{ $summary['today_orders'] }}</div>
                <span class="text-[10px] text-blue-600 font-semibold subtext">Sedang diproses</span>
            </div>
            <div class="p-4 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm space-y-1 kpi-card-box">
                <span class="text-[11px] font-semibold text-slate-500 uppercase block adaptive-label">Customer Aktif</span>
                <div class="text-xl font-extrabold text-purple-600 dark:text-purple-400 stat-value">{{ $summary['active_customers'] }}</div>
                <span class="text-[10px] text-purple-600 font-semibold subtext">Akun terverifikasi</span>
            </div>
            <div class="p-4 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm space-y-1 kpi-card-box">
                <span class="text-[11px] font-semibold text-slate-500 uppercase block adaptive-label">Menu Terlaris</span>
                <div class="text-xs font-bold text-slate-900 dark:text-white truncate stat-value" title="{{ $summary['top_menu_name'] }}">{{ $summary['top_menu_name'] }}</div>
                <span class="text-[10px] font-extrabold text-amber-600 block subtext">{{ $summary['top_menu_qty'] }} Porsi Terjual</span>
            </div>
            <div class="p-4 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm space-y-1 kpi-card-box">
                <span class="text-[11px] font-semibold text-slate-500 uppercase block adaptive-label">Rata-Rata Nilai</span>
                <div class="text-base font-extrabold text-slate-900 dark:text-white stat-value">{{ format_idr($summary['avg_order_value']) }}</div>
                <span class="text-[10px] text-slate-400 block subtext">Per transaksi order</span>
            </div>
        </div>

    @elseif($wId === 'A-CHARTS')
        {{-- GRAFIK PESANAN (7 HARI) & GRAFIK PENDAPATAN (PER BULAN) --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 analytics-content-box">
            <div class="p-6 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                <div class="flex items-center justify-between flex-between-header">
                    <div>
                        <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider adaptive-label">Grafik Pesanan (7 Hari Terakhir)</h3>
                        <p class="text-[11px] text-slate-400 subtext">Tren volume transaksi pemesanan harian</p>
                    </div>
                    <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-lg subtext">Puncak: Jumat</span>
                </div>
                <div class="h-64 relative flex items-center justify-center">
                    <canvas id="weeklyOrdersChart"></canvas>
                </div>
            </div>
            <div class="p-6 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                <div class="flex items-center justify-between flex-between-header">
                    <div>
                        <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider adaptive-label">Grafik Pendapatan (Per Bulan)</h3>
                        <p class="text-[11px] text-slate-400 subtext">Pertumbuhan omset pendapatan (dalam Jutaan Rp)</p>
                    </div>
                    <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-lg subtext">Tren Positif 📈</span>
                </div>
                <div class="h-64 relative flex items-center justify-center">
                    <canvas id="monthlyRevenueChart"></canvas>
                </div>
            </div>
        </div>

    @elseif($wId === 'A-TOP-MENU')
        {{-- TOP 5 MENU TERLARIS & DISTRIBUSI STATUS --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 analytics-content-box">
            <div class="lg:col-span-2 p-6 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                <div class="flex items-center justify-between flex-between-header">
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center space-x-2 adaptive-label">
                        <span>🔥</span><span>Top 5 Menu Terlaris</span>
                    </h3>
                    <span class="text-[11px] text-slate-400 font-semibold subtext">Total Porsi Terjual</span>
                </div>
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
            </div>
            <div class="p-6 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider text-center adaptive-label">Distribusi Status Pesanan</h3>
                <div class="h-48 relative flex items-center justify-center">
                    <canvas id="orderStatusChart"></canvas>
                </div>
                <div class="grid grid-cols-2 gap-2 text-[11px] pt-2 border-t border-slate-100 dark:border-slate-800 subtext">
                    <div class="flex items-center space-x-2"><span class="w-3 h-3 rounded-full bg-amber-500 shrink-0"></span><span class="text-slate-600 dark:text-slate-400">Menunggu: 12</span></div>
                    <div class="flex items-center space-x-2"><span class="w-3 h-3 rounded-full bg-blue-500 shrink-0"></span><span class="text-slate-600 dark:text-slate-400">Diproses: 31</span></div>
                    <div class="flex items-center space-x-2"><span class="w-3 h-3 rounded-full bg-purple-500 shrink-0"></span><span class="text-slate-600 dark:text-slate-400">Dikirim: 18</span></div>
                    <div class="flex items-center space-x-2"><span class="w-3 h-3 rounded-full bg-emerald-500 shrink-0"></span><span class="text-slate-600 dark:text-slate-400">Selesai: 64</span></div>
                </div>
            </div>
        </div>

    @elseif($wId === 'A-ACTIVITY')
        {{-- AKTIVITAS SISTEM TERBARU --}}
        <div class="p-6 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4 analytics-content-box">
            <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center space-x-2 adaptive-label">
                <span>⚡</span><span>Aktivitas Sistem Terbaru</span>
            </h3>
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
        </div>

    @elseif($wId === 'A-SYSTEM')
        {{-- RINGKASAN MASTER SISTEM --}}
        <div class="p-6 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4 analytics-content-box">
            <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider adaptive-label">Ringkasan Master Sistem</h3>
            <div class="space-y-3 text-xs">
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex-between-header">
                    <span class="text-slate-600 dark:text-slate-400 font-semibold">Jumlah Menu Katering</span>
                    <span class="font-extrabold text-emerald-600 dark:text-emerald-400 stat-value">{{ $systemTotals['menu_count'] }} Paket</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex-between-header">
                    <span class="text-slate-600 dark:text-slate-400 font-semibold">Jumlah Customer Terdaftar</span>
                    <span class="font-extrabold text-purple-600 dark:text-purple-400 stat-value">{{ $systemTotals['customer_count'] }} Akun</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex-between-header">
                    <span class="text-slate-600 dark:text-slate-400 font-semibold">Jumlah Admin Pengelola</span>
                    <span class="font-extrabold text-slate-900 dark:text-white stat-value">{{ $systemTotals['admin_count'] }} Personil</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex-between-header">
                    <span class="text-slate-600 dark:text-slate-400 font-semibold">Jumlah Kurir Pengiriman</span>
                    <span class="font-extrabold text-blue-600 dark:text-blue-400 stat-value">{{ $systemTotals['courier_count'] }} Armada</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex-between-header">
                    <span class="text-slate-600 dark:text-slate-400 font-semibold">Jumlah Invoice Terbit</span>
                    <span class="font-extrabold text-amber-600 dark:text-amber-400 stat-value">{{ $systemTotals['invoice_count'] }} Invoice</span>
                </div>
            </div>
        </div>
    @endif

    </div> {{-- End Inner Visual Component Card (#analytics-widget-card-{wId}) --}}
</div>

