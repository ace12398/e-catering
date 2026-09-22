<x-dashboard-layout>
    <x-slot name="title">Keuangan & Tagihan — E-Catering</x-slot>
    <x-slot name="toolbarTitle">Keuangan & Tagihan</x-slot>

    {{-- SortableJS untuk KPI Reorder --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

    <style>
        /* Drag-and-Drop Sortable Visual Feedback & Dropzone */
        .finance-sortable-ghost {
            opacity: 0.35 !important;
            border: 2px dashed #f59e0b !important;
            background-color: rgba(245, 158, 11, 0.12) !important;
            border-radius: 1.25rem !important;
            box-shadow: inset 0 0 16px rgba(245, 158, 11, 0.25) !important;
            transform: scale(0.98);
        }
        .finance-sortable-chosen {
            cursor: grabbing !important;
        }
        .finance-sortable-chosen * {
            cursor: grabbing !important;
        }
        .finance-sortable-drag {
            opacity: 0.95 !important;
            transform: rotate(1.5deg) scale(1.03) !important;
            box-shadow: 0 25px 30px -5px rgba(0, 0, 0, 0.3), 0 15px 15px -5px rgba(0, 0, 0, 0.2) !important;
            z-index: 9999 !important;
        }
        .finance-kpi-drag-handle {
            user-select: none;
            touch-action: none;
        }
    </style>

    <div class="max-w-6xl mx-auto space-y-6" id="finance-root">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 text-emerald-800 text-xs font-bold">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 text-rose-800 text-xs font-bold">❌ {{ session('error') }}</div>
        @endif

        {{-- TOAST --}}
        <div id="finance-toast" class="hidden fixed bottom-6 right-6 z-50 p-4 rounded-xl bg-slate-900 text-white shadow-2xl border border-slate-700 flex items-center space-x-3 transition-all transform translate-y-4 opacity-0">
            <span id="finance-toast-icon" class="text-emerald-400 text-lg">✓</span>
            <span id="finance-toast-message" class="text-xs font-medium">KPI berhasil diperbarui.</span>
        </div>

        {{-- EDIT MODE BAR --}}
        <div id="finance-edit-bar" class="hidden sticky top-4 z-40 p-4 rounded-xl bg-amber-900/90 backdrop-blur border-2 border-amber-500 text-white shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center space-x-3">
                <span class="p-2 rounded-lg bg-amber-800 text-amber-300 font-mono text-sm">✨</span>
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-300">Mode Penyesuaian Tampilan Aktif — Keuangan</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-800 text-amber-200">Drag & Drop Aktif</span>
                    </div>
                    <p class="text-xs text-amber-100 mt-0.5">Ubah bentuk (shape), lebar, tinggi, dan geser kartu KPI untuk mengubah susunan tata letak secara langsung.</p>
                </div>
            </div>
            <div class="flex items-center space-x-2 shrink-0">
                <button type="button" onclick="cancelFinanceLayout()" class="px-3.5 py-2 rounded-lg bg-amber-950 hover:bg-amber-800 text-amber-200 font-semibold text-xs transition-colors border border-amber-700">✕ Batal</button>
                <button type="button" onclick="resetFinanceLayout()" class="px-3.5 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs transition-colors border border-slate-600">🔄 Reset Default</button>
                <button type="button" onclick="saveFinanceLayout()" class="px-4 py-2 rounded-lg bg-white text-slate-900 hover:bg-slate-100 font-extrabold text-xs shadow-md transition-colors flex items-center space-x-1.5"><span>💾 Simpan Tampilan</span></button>
            </div>
        </div>

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Keuangan &amp; Verifikasi Pembayaran</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Verifikasi bukti pembayaran dan pantau status tagihan.</p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Total: {{ $invoices->total() }} Invoice
                </span>
                {{-- TOMBOL SESUAIKAN TAMPILAN --}}
                <button type="button" id="finance-btn-edit" onclick="toggleFinanceEditMode()"
                        class="h-9 px-4 rounded-lg bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs transition-colors flex items-center space-x-2 shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <span id="finance-btn-text">⚙️ Sesuaikan Tampilan</span>
                </button>
            </div>
        </div>

        {{-- KPI Cards (SORTABLE dalam Edit Mode) --}}
        @php
            $allInvoices = $invoices->getCollection();
            $kpiData = [
                'lunas'      => ['label' => 'Lunas',                'icon' => '✅', 'bg' => 'bg-emerald-50 dark:bg-emerald-950/30 border-emerald-200', 'text' => 'text-emerald-700', 'sub' => 'text-emerald-600', 'count' => $allInvoices->whereIn('status', ['lunas','paid'])->count(),      'total' => format_idr($allInvoices->whereIn('status', ['lunas','paid'])->sum('grand_total'))],
                'menunggu'   => ['label' => 'Menunggu Verifikasi',  'icon' => '⏳', 'bg' => 'bg-blue-50 dark:bg-blue-950/30 border-blue-200',           'text' => 'text-blue-700',    'sub' => 'text-blue-600',    'count' => $allInvoices->whereIn('status', ['menunggu_verifikasi'])->count(),           'total' => format_idr($allInvoices->whereIn('status', ['menunggu_verifikasi'])->sum('grand_total'))],
                'belum_bayar'=> ['label' => 'Belum Dibayar',        'icon' => '🕐', 'bg' => 'bg-amber-50 dark:bg-amber-950/30 border-amber-200',         'text' => 'text-amber-700',   'sub' => 'text-amber-600',   'count' => $allInvoices->whereIn('status', ['belum_dibayar','unpaid'])->count(),        'total' => format_idr($allInvoices->whereIn('status', ['belum_dibayar','unpaid'])->sum('grand_total'))],
                'ditolak'    => ['label' => 'Ditolak',              'icon' => '✕', 'bg' => 'bg-rose-50 dark:bg-rose-950/30 border-rose-200',           'text' => 'text-rose-700',    'sub' => 'text-rose-600',    'count' => $allInvoices->whereIn('status', ['ditolak'])->count(),                      'total' => format_idr($allInvoices->whereIn('status', ['ditolak'])->sum('grand_total'))],
            ];
            $financeKpiOrder = $financeKpiOrder ?? ['lunas', 'menunggu', 'belum_bayar', 'ditolak'];
        @endphp
        <div id="finance-kpi-container" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 items-start">
            @foreach($financeKpiOrder as $kpiKey)
                @if(isset($kpiData[$kpiKey]))
                @php $kd = $kpiData[$kpiKey]; @endphp
                <div class="flex flex-col items-center space-y-2 w-full transition-all" data-kpi-wrapper="{{ $kpiKey }}">
                    {{-- 1. IN-PLACE EDITOR LAYER --}}
                    <div class="finance-style-panel hidden w-full max-w-full p-2.5 rounded-xl bg-slate-900/90 text-white space-y-2 text-[11px] shadow-lg border border-slate-700/60 backdrop-blur z-20">
                        <div class="flex items-center justify-between font-bold border-b border-slate-700 pb-1.5 text-[10px]">
                            <div class="flex items-center space-x-1.5">
                                <span class="finance-kpi-drag-handle cursor-grab active:cursor-grabbing px-2 py-0.5 rounded bg-amber-600 hover:bg-amber-500 text-white text-[10px] font-bold flex items-center space-x-1 shadow-sm transition-colors select-none" title="Tahan dan geser untuk memindahkan urutan kartu">
                                    <span>⠿</span>
                                    <span>Geser</span>
                                </span>
                                <span class="text-amber-400">Atur: {{ $kd['label'] }}</span>
                            </div>
                            <span class="text-[9px] text-slate-400 font-mono">({{ $kpiKey }})</span>
                        </div>
                        {{-- Shape Picker --}}
                        <div>
                            <span class="text-[9px] text-slate-300 block mb-1">Bentuk:</span>
                            <div class="grid grid-cols-6 gap-1">
                                @php
                                    $fShapes = [
                                        'rectangle' => 'Kotak',
                                        'rounded'   => 'Bulat',
                                        'sharp'     => 'Tajam',
                                        'pill'      => 'Pil',
                                        'circle'    => 'Lingkaran',
                                        'hexagon'   => 'Heksagon',
                                    ];
                                @endphp
                                @foreach($fShapes as $sid => $slabel)
                                    <button type="button"
                                        data-shape="{{ $sid }}"
                                        onclick="setFinanceKpiShape('{{ $kpiKey }}', '{{ $sid }}')"
                                        class="f-shape-btn-{{ $kpiKey }} px-1 py-1 rounded text-[9px] font-semibold border text-center hover:bg-amber-500 hover:text-white transition-colors bg-white text-slate-800 border-slate-300"
                                    >{{ $slabel }}</button>
                                @endforeach
                            </div>
                        </div>
                        {{-- Width & Height Controls --}}
                        <div class="grid grid-cols-2 gap-2 pt-1 border-t border-slate-700/50">
                            <div>
                                <div class="flex justify-between text-[9px] text-slate-300 mb-0.5">
                                    <span>Lebar (W):</span>
                                    <span id="f-wval-{{ $kpiKey }}">240px</span>
                                </div>
                                <input type="range" min="140" max="400" step="10" value="240" id="f-width-{{ $kpiKey }}"
                                    oninput="document.getElementById('f-wval-{{ $kpiKey }}').textContent=this.value+'px'; onFinanceSizeInput('{{ $kpiKey }}', 'width', this.value)"
                                    class="w-full h-1 bg-slate-700 rounded accent-amber-500 cursor-pointer">
                            </div>
                            <div>
                                <div class="flex justify-between text-[9px] text-slate-300 mb-0.5">
                                    <span>Tinggi (H):</span>
                                    <span id="f-hval-{{ $kpiKey }}">120px</span>
                                </div>
                                <input type="range" min="80" max="300" step="10" value="120" id="f-height-{{ $kpiKey }}"
                                    oninput="document.getElementById('f-hval-{{ $kpiKey }}').textContent=this.value+'px'; onFinanceSizeInput('{{ $kpiKey }}', 'height', this.value)"
                                    class="w-full h-1 bg-slate-700 rounded accent-amber-500 cursor-pointer">
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-[9px] font-mono text-amber-400 border-t border-slate-800 pt-1">
                            <span id="f-size-display-{{ $kpiKey }}">240 × 120 px</span>
                            <button type="button" onclick="resetSingleFinanceKpi('{{ $kpiKey }}')" class="px-1.5 py-0.5 rounded text-[9px] font-semibold bg-amber-800/80 hover:bg-amber-700 text-white border border-amber-600 transition-colors" title="Reset komponen ini">🔄 Reset</button>
                        </div>
                    </div>

                    {{-- 2. VISUAL KPI COMPONENT --}}
                    <div data-kpi-id="{{ $kpiKey }}" class="finance-kpi-card finance-content-box p-4 rounded-2xl {{ $kd['bg'] }} border space-y-1 relative transition-all duration-300 overflow-hidden flex flex-col justify-between w-full">
                        {{-- CARD DRAG HANDLE BADGE --}}
                        <div class="finance-kpi-drag-handle hidden absolute top-2 right-2 z-10 px-2 py-0.5 rounded-md bg-amber-500/20 hover:bg-amber-500 text-amber-800 dark:text-amber-200 hover:text-white border border-amber-400/40 cursor-grab active:cursor-grabbing transition-colors flex items-center space-x-1 select-none shadow-sm" title="Tahan dan geser untuk memindahkan urutan kartu">
                            <svg class="w-3 h-3 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 8h16M4 16h16"></path></svg>
                            <span class="text-[9px] font-extrabold font-mono pointer-events-none">GESER</span>
                        </div>
                        <div class="flex items-center justify-between flex-between-header">
                            <span class="text-[11px] font-semibold {{ $kd['text'] }} uppercase adaptive-label flex items-center space-x-1">
                                <span class="adaptive-icon text-sm">{{ $kd['icon'] }}</span>
                                <span>{{ $kd['label'] }}</span>
                            </span>
                        </div>
                        <div class="text-2xl font-extrabold {{ $kd['text'] }} stat-value">{{ $kd['count'] }}</div>
                        <p class="text-[11px] {{ $kd['sub'] }} subtext">{{ $kd['total'] }}</p>
                    </div>
                </div>
                @endif
            @endforeach
        </div>

        {{-- Invoice List --}}
        <div class="space-y-4">
            @forelse($invoices as $invoice)
            @php
                $statusConfig = [
                    'belum_dibayar'       => ['label'=>'Belum Dibayar','bg'=>'bg-amber-100','text'=>'text-amber-700','icon'=>'🕐'],
                    'menunggu_verifikasi' => ['label'=>'Menunggu Verifikasi','bg'=>'bg-blue-100','text'=>'text-blue-700','icon'=>'📋'],
                    'lunas'               => ['label'=>'Lunas','bg'=>'bg-emerald-100','text'=>'text-emerald-700','icon'=>'✅'],
                    'ditolak'             => ['label'=>'Ditolak','bg'=>'bg-rose-100','text'=>'text-rose-700','icon'=>'✕'],
                    'unpaid'              => ['label'=>'Belum Dibayar','bg'=>'bg-amber-100','text'=>'text-amber-700','icon'=>'🕐'],
                    'paid'                => ['label'=>'Lunas','bg'=>'bg-emerald-100','text'=>'text-emerald-700','icon'=>'✅'],
                ];
                $sc = $statusConfig[$invoice->status] ?? ['label'=>strtoupper($invoice->status),'bg'=>'bg-slate-100','text'=>'text-slate-700','icon'=>'📄'];
                $canAct = in_array($invoice->status, ['menunggu_verifikasi', 'unpaid']);
                $invId = "finance_invoice_" . $invoice->id;
            @endphp
                <div class="flex flex-col items-center space-y-2 w-full" data-invoice-wrapper="{{ $invId }}">
                    {{-- 1. IN-PLACE EDITOR CONTROL LAYER FOR THIS INVOICE ITEM --}}
                    <div class="finance-invoice-style-panel hidden w-full max-w-full p-2.5 rounded-xl bg-slate-900/90 text-white space-y-2 text-[11px] shadow-lg border border-slate-700/60 backdrop-blur z-20">
                        <div class="flex items-center justify-between font-bold border-b border-slate-700 pb-1 text-[10px]">
                            <span class="text-amber-400">Edit Invoice: {{ $invoice->invoice_number }}</span>
                            <span class="text-[9px] text-slate-400">({{ $invId }})</span>
                        </div>
                        <div>
                            <span class="text-[9px] text-slate-300 block mb-1">Bentuk Layout:</span>
                            <div class="grid grid-cols-6 gap-1">
                                @foreach(['rectangle'=>'Square','rounded'=>'Round','sharp'=>'Sharp','pill'=>'Pill','circle'=>'Circle','hexagon'=>'Hex'] as $sid => $slabel)
                                    <button type="button" data-shape="{{ $sid }}" onclick="setFinanceInvoiceShape('{{ $invId }}', '{{ $sid }}')" class="fi-shape-btn-{{ $invId }} px-1 py-1 rounded text-[9px] font-semibold border text-center hover:bg-amber-500 hover:text-white transition-colors bg-white text-slate-800 border-slate-300">{{ $slabel }}</button>
                                @endforeach
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 pt-1 border-t border-slate-700/50">
                            <div>
                                <div class="flex justify-between text-[9px] text-slate-300 mb-0.5"><span>Lebar (W):</span><span id="fi-wval-{{ $invId }}">600px</span></div>
                                <input type="range" min="200" max="800" step="10" value="600" id="fi-width-{{ $invId }}" oninput="document.getElementById('fi-wval-{{ $invId }}').textContent=this.value+'px'; onFinanceInvoiceSizeInput('{{ $invId }}', 'width', this.value)" class="w-full h-1 bg-slate-700 rounded accent-amber-500 cursor-pointer">
                            </div>
                            <div>
                                <div class="flex justify-between text-[9px] text-slate-300 mb-0.5"><span>Tinggi (H):</span><span id="fi-hval-{{ $invId }}">120px</span></div>
                                <input type="range" min="80" max="400" step="10" value="120" id="fi-height-{{ $invId }}" oninput="document.getElementById('fi-hval-{{ $invId }}').textContent=this.value+'px'; onFinanceInvoiceSizeInput('{{ $invId }}', 'height', this.value)" class="w-full h-1 bg-slate-700 rounded accent-amber-500 cursor-pointer">
                            </div>
                        </div>
                        <div class="text-[9px] text-center font-mono font-semibold text-amber-400 border-t border-slate-800 pt-1"><span id="fi-size-display-{{ $invId }}">600 × 120 px</span></div>
                    </div>

                    {{-- 2. VISUAL INVOICE COMPONENT --}}
                    <div data-invoice-id="{{ $invId }}" class="finance-invoice-card finance-content-box p-5 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm hover:border-emerald-300 transition-all duration-300 overflow-hidden w-full">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 flex-between-header">
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 rounded-xl {{ $sc['bg'] }} flex items-center justify-center text-lg flex-shrink-0 icon-container">
                                    {{ $sc['icon'] }}
                                </div>
                                <div>
                                    <div class="flex items-center space-x-2 flex-wrap gap-1">
                                        <span class="text-sm font-bold text-slate-900 dark:text-white adaptive-label">{{ $invoice->invoice_number }}</span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase {{ $sc['bg'] }} {{ $sc['text'] }} border border-current status-badge">
                                            {{ $sc['label'] }}
                                        </span>
                                        @if($invoice->payment_proof)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-700 border border-indigo-200 subtext">📎 Ada Bukti</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-slate-500 mt-0.5 secondary-info">
                                        {{ $invoice->customer?->name ?? 'Pelanggan' }}
                                        @if($invoice->customer?->profile?->company_name)
                                            • {{ $invoice->customer->profile->company_name }}
                                        @endif
                                    </p>
                                    <p class="text-[11px] text-slate-400 subtext">
                                        Pesanan: {{ $invoice->order?->order_number ?? '-' }}
                                        @if($invoice->due_date) • Tempo: {{ $invoice->due_date->format('d M Y') }} @endif
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 sm:flex-col sm:items-end">
                                <div class="text-sm font-extrabold stat-value {{ in_array($invoice->status, ['lunas','paid']) ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-800 dark:text-slate-200' }}">
                                    {{ format_idr($invoice->grand_total) }}
                                </div>
                                <div class="flex gap-2 action-buttons">
                                    <a href="{{ route('finance.show', $invoice->id) }}"
                                       class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-slate-750 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors">
                                        Detail
                                    </a>
                                    @if($canAct)
                                    <a href="{{ route('finance.show', $invoice->id) }}"
                                       class="px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-500 hover:bg-emerald-600 text-white transition-colors">
                                        Verifikasi
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-16 text-center rounded-2xl bg-white dark:bg-slate-850 border border-slate-200">
                    <div class="text-4xl mb-3">📄</div>
                    <p class="text-slate-500 text-sm font-medium">Belum ada tagihan keuangan.</p>
                </div>
            @endforelse

            <div class="pt-2">{{ $invoices->links() }}</div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.financeIsEditMode  = false;
            window.financeLastSaved   = @json($financeKpiOrder);
            window.financeCurrent     = [...window.financeLastSaved];
            window.financeSortable    = null;

            const fDefaultStyles = @json(\App\Models\WorkspacePreference::getDefaultLayoutMatrix()['component_styles']['finance'] ?? []);
            const fSavedStyles   = @json($financeComponentStyles ?? []);
            window.financeCurrentStyles   = Object.assign({}, fDefaultStyles, fSavedStyles);
            window.financeLastSavedStyles = JSON.parse(JSON.stringify(window.financeCurrentStyles));

            const container = document.getElementById('finance-kpi-container');
            if (container && typeof Sortable !== 'undefined') {
                window.financeSortable = new Sortable(container, {
                    handle:     '.finance-kpi-drag-handle',
                    draggable:  '[data-kpi-wrapper]',
                    animation:  250,
                    ghostClass: 'finance-sortable-ghost',
                    chosenClass:'finance-sortable-chosen',
                    dragClass:  'finance-sortable-drag',
                    disabled:   true,
                    onEnd: function() {
                        window.financeCurrent = Array.from(
                            container.querySelectorAll('[data-kpi-wrapper]')
                        ).map(el => el.dataset.kpiWrapper);
                    },
                });
            }

            ['lunas','menunggu','belum_bayar','ditolak'].forEach(kId => {
                const style = window.financeCurrentStyles[kId];
                if (style) applyFinanceKpiStyle(kId, style);
            });
            document.querySelectorAll('[data-invoice-id]').forEach(el => {
                const invId = el.dataset.invoiceId;
                const style = window.financeCurrentStyles[invId];
                if (style) applyFinanceInvoiceStyle(invId, style);
            });
        });

        function toggleFinanceEditMode() {
            window.financeIsEditMode = !window.financeIsEditMode;
            const bar = document.getElementById('finance-edit-bar');
            const btn = document.getElementById('finance-btn-text');

            if (window.financeIsEditMode) {
                bar.classList.remove('hidden');
                btn.textContent = '✕ Keluar Mode Penyesuaian';
                document.querySelectorAll('.finance-kpi-drag-handle').forEach(el => el.classList.remove('hidden'));
                document.querySelectorAll('.finance-style-panel').forEach(el => el.classList.remove('hidden'));
                document.querySelectorAll('.finance-invoice-style-panel').forEach(el => el.classList.remove('hidden'));
                document.querySelectorAll('.finance-kpi-card, .finance-invoice-card').forEach(el => {
                    el.classList.add('ring-2', 'ring-amber-400/60');
                });
                updateFinanceStyleDisplays();
            } else {
                bar.classList.add('hidden');
                btn.textContent = '⚙️ Sesuaikan Tampilan';
                document.querySelectorAll('.finance-kpi-drag-handle').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('.finance-style-panel').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('.finance-invoice-style-panel').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('.finance-kpi-card, .finance-invoice-card').forEach(el => {
                    el.classList.remove('ring-2', 'ring-amber-400/60');
                });
            }
            if (window.financeSortable) {
                window.financeSortable.option('disabled', !window.financeIsEditMode);
            }
        }

        function saveFinanceLayout() {
            fetch('{{ route("workspace.save-layout") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ layout_matrix: {
                    finance_kpi_order: window.financeCurrent,
                    component_styles: { finance: JSON.parse(JSON.stringify(window.financeCurrentStyles)) }
                }})
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    window.financeLastSaved       = [...window.financeCurrent];
                    window.financeLastSavedStyles = JSON.parse(JSON.stringify(window.financeCurrentStyles));
                    showFinanceToast('✓ Tampilan Keuangan berhasil disimpan.', 'success');
                    toggleFinanceEditMode();
                } else {
                    showFinanceToast('Gagal menyimpan: ' + (data.message || 'Error'), 'error');
                }
            })
            .catch(() => showFinanceToast('Kesalahan koneksi.', 'error'));
        }

        function cancelFinanceLayout() {
            window.financeCurrent       = [...window.financeLastSaved];
            window.financeCurrentStyles = JSON.parse(JSON.stringify(window.financeLastSavedStyles));
            const container = document.getElementById('finance-kpi-container');
            if (container) {
                const wrappers = {};
                container.querySelectorAll('[data-kpi-wrapper]').forEach(w => { wrappers[w.dataset.kpiWrapper] = w; });
                window.financeLastSaved.forEach(key => { if (wrappers[key]) container.appendChild(wrappers[key]); });
            }
            Object.keys(window.financeCurrentStyles).forEach(kId => {
                if (kId.startsWith('finance_invoice_')) {
                    applyFinanceInvoiceStyle(kId, window.financeCurrentStyles[kId]);
                } else {
                    applyFinanceKpiStyle(kId, window.financeCurrentStyles[kId]);
                }
            });
            updateFinanceStyleDisplays();
            showFinanceToast('Perubahan dibatalkan.', 'info');
            toggleFinanceEditMode();
        }

        function resetFinanceLayout() {
            if (!confirm('Reset tampilan keuangan ke default?')) return;
            fetch('{{ route("workspace.reset-layout") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ page: 'finance' })
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    const order = data.layout_matrix?.finance_kpi_order || ['lunas','menunggu','belum_bayar','ditolak'];
                    window.financeLastSaved = [...order];
                    window.financeCurrent   = [...order];
                    const resetStyles = (data.layout_matrix?.component_styles || {}).finance || _financeDefaults;
                    window.financeCurrentStyles   = Object.assign({}, resetStyles);
                    window.financeLastSavedStyles = JSON.parse(JSON.stringify(window.financeCurrentStyles));
                    
                    const container = document.getElementById('finance-kpi-container');
                    if (container) {
                        const wrappers = {};
                        container.querySelectorAll('[data-kpi-wrapper]').forEach(w => { wrappers[w.dataset.kpiWrapper] = w; });
                        order.forEach(key => { if (wrappers[key]) container.appendChild(wrappers[key]); });
                    }
                    
                    ['lunas','menunggu','belum_bayar','ditolak'].forEach(kId => {
                        applyFinanceKpiStyle(kId, window.financeCurrentStyles[kId] || _financeDefaults[kId]);
                    });
                    document.querySelectorAll('[data-invoice-id]').forEach(el => {
                        applyFinanceInvoiceStyle(el.dataset.invoiceId, window.financeCurrentStyles[el.dataset.invoiceId] || { shape: 'rectangle', width: 600, height: 120 });
                    });

                    updateFinanceStyleDisplays();
                    showFinanceToast('✓ Tampilan dikembalikan ke default.', 'success');
                    if (window.financeIsEditMode) toggleFinanceEditMode();
                }
            })
            .catch(() => showFinanceToast('Gagal reset.', 'error'));
        }

        function showFinanceToast(msg, type = 'success') {
            const toast = document.getElementById('finance-toast');
            const msgEl = document.getElementById('finance-toast-message');
            const icon  = document.getElementById('finance-toast-icon');
            msgEl.textContent = msg;
            icon.textContent  = type === 'success' ? '✓' : (type === 'error' ? '✕' : 'ℹ');
            toast.classList.remove('hidden', 'translate-y-4', 'opacity-0');
            setTimeout(() => {
                toast.classList.add('translate-y-4', 'opacity-0');
                setTimeout(() => toast.classList.add('hidden'), 300);
            }, 3000);
        }

        function setFinanceKpiShape(kpiId, shape) {
            if (!window.financeCurrentStyles[kpiId]) window.financeCurrentStyles[kpiId] = {};
            window.financeCurrentStyles[kpiId].shape = shape;
            applyFinanceKpiStyle(kpiId, window.financeCurrentStyles[kpiId]);
            document.querySelectorAll('.f-shape-btn-' + kpiId).forEach(btn => {
                const isActive = btn.dataset.shape === shape;
                btn.classList.toggle('bg-amber-500', isActive);
                btn.classList.toggle('text-white', isActive);
                btn.classList.toggle('border-amber-600', isActive);
                btn.classList.toggle('bg-white', !isActive);
                btn.classList.toggle('text-slate-800', !isActive);
                btn.classList.toggle('border-slate-300', !isActive);
            });
        }

        function onFinanceSizeInput(kpiId, dim, value) {
            const v = parseInt(value);
            if (isNaN(v)) return;
            if (!window.financeCurrentStyles[kpiId]) window.financeCurrentStyles[kpiId] = {};
            window.financeCurrentStyles[kpiId][dim] = v;
            applyFinanceKpiStyle(kpiId, window.financeCurrentStyles[kpiId]);
        }

        function applyFinanceKpiStyle(kpiId, style) {
            if (!style) return;
            const el = document.querySelector('[data-kpi-id="' + kpiId + '"]');
            if (!el) return;
            const reqWidth  = parseInt(style.width)  || 240;
            const reqHeight = parseInt(style.height) || 120;
            const shape     = style.shape || 'rectangle';
            const bounds = { minW: 140, maxW: 400, minH: 80, maxH: 300 };
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
                el.style.maxHeight = `${clampedH}px`;
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
                    default:
                        const radius = parseInt(style.border_radius) || 12;
                        el.style.borderRadius = `${radius}px`;
                }
            }

            if (window.adaptComponentContent) window.adaptComponentContent(el, shape, clampedW, clampedH);

            const actualRect = el.getBoundingClientRect();
            const actualW    = Math.round(actualRect.width);
            const actualH    = Math.round(actualRect.height);

            const display = document.getElementById('f-size-display-' + kpiId);
            if (display) display.textContent = `${actualW} × ${actualH} px (Req: ${reqWidth}×${reqHeight})`;

            const wInput = document.getElementById('f-width-' + kpiId);
            const hInput = document.getElementById('f-height-' + kpiId);
            const wVal   = document.getElementById('f-wval-'   + kpiId);
            const hVal   = document.getElementById('f-hval-'   + kpiId);
            if (wInput) wInput.value = reqWidth;
            if (hInput) hInput.value = reqHeight;
            if (wVal)   wVal.textContent = `${reqWidth}px`;
            if (hVal)   hVal.textContent = `${reqHeight}px`;
        }

        function setFinanceInvoiceShape(invId, shape) {
            if (!window.financeCurrentStyles[invId]) window.financeCurrentStyles[invId] = {};
            window.financeCurrentStyles[invId].shape = shape;
            applyFinanceInvoiceStyle(invId, window.financeCurrentStyles[invId]);
            document.querySelectorAll('.fi-shape-btn-' + invId).forEach(btn => {
                const isActive = btn.dataset.shape === shape;
                btn.classList.toggle('bg-amber-500', isActive);
                btn.classList.toggle('text-white', isActive);
                btn.classList.toggle('border-amber-600', isActive);
                btn.classList.toggle('bg-white', !isActive);
                btn.classList.toggle('text-slate-800', !isActive);
                btn.classList.toggle('border-slate-300', !isActive);
            });
        }

        function onFinanceInvoiceSizeInput(invId, dim, value) {
            const v = parseInt(value);
            if (isNaN(v)) return;
            if (!window.financeCurrentStyles[invId]) window.financeCurrentStyles[invId] = {};
            window.financeCurrentStyles[invId][dim] = v;
            applyFinanceInvoiceStyle(invId, window.financeCurrentStyles[invId]);
        }

        function applyFinanceInvoiceStyle(invId, style) {
            if (!style) return;
            const el = document.querySelector('[data-invoice-id="' + invId + '"]');
            if (!el) return;
            const reqWidth  = parseInt(style.width)  || 600;
            const reqHeight = parseInt(style.height) || 120;
            const shape     = style.shape || 'rectangle';
            const bounds = { minW: 160, maxW: 800, minH: 80, maxH: 500 };
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
                el.style.maxHeight = `${clampedH}px`;
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
                    default:
                        const radius = parseInt(style.border_radius) || 16;
                        el.style.borderRadius = `${radius}px`;
                }
            }

            if (window.adaptComponentContent) window.adaptComponentContent(el, shape, clampedW, clampedH);

            const actualRect = el.getBoundingClientRect();
            const actualW    = Math.round(actualRect.width);
            const actualH    = Math.round(actualRect.height);

            const display = document.getElementById('fi-size-display-' + invId);
            if (display) display.textContent = `${actualW} × ${actualH} px (Req: ${reqWidth}×${reqHeight})`;

            const wInput = document.getElementById('fi-width-' + invId);
            const hInput = document.getElementById('fi-height-' + invId);
            const wVal   = document.getElementById('fi-wval-'   + invId);
            const hVal   = document.getElementById('fi-hval-'   + invId);
            if (wInput) wInput.value = reqWidth;
            if (hInput) hInput.value = reqHeight;
            if (wVal)   wVal.textContent = `${reqWidth}px`;
            if (hVal)   hVal.textContent = `${reqHeight}px`;
        }

        function updateFinanceStyleDisplays() {
            ['lunas','menunggu','belum_bayar','ditolak'].forEach(kId => {
                const style = window.financeCurrentStyles[kId] || { width: 240, height: 120 };
                const wInput = document.getElementById('f-width-'  + kId);
                const hInput = document.getElementById('f-height-' + kId);
                if (wInput) wInput.value = style.width || 240;
                if (hInput) hInput.value = style.height || 120;
                if (style.shape) setFinanceKpiShape(kId, style.shape);
            });
            document.querySelectorAll('[data-invoice-id]').forEach(el => {
                const invId = el.dataset.invoiceId;
                const style = window.financeCurrentStyles[invId] || { width: 600, height: 120 };
                const wInput = document.getElementById('fi-width-'  + invId);
                const hInput = document.getElementById('fi-height-' + invId);
                if (wInput) wInput.value = style.width || 600;
                if (hInput) hInput.value = style.height || 120;
                if (style.shape) setFinanceInvoiceShape(invId, style.shape);
            });
        }

        const _financeDefaults = {
            'lunas': { shape: 'rectangle', width: 240, height: 120 },
            'menunggu': { shape: 'rectangle', width: 240, height: 120 },
            'belum_bayar': { shape: 'rectangle', width: 240, height: 120 },
            'ditolak': { shape: 'rectangle', width: 240, height: 120 }
        };

        function resetSingleFinanceKpi(kpiKey) {
            const defaults = _financeDefaults[kpiKey] || { shape: 'rectangle', width: 240, height: 120 };
            window.financeCurrentStyles[kpiKey] = Object.assign({}, defaults);
            applyFinanceKpiStyle(kpiKey, window.financeCurrentStyles[kpiKey]);

            // Also reposition this card to its natural relative order index
            const defaultOrder = ['lunas', 'menunggu', 'belum_bayar', 'ditolak'];
            const targetDefaultIdx = defaultOrder.indexOf(kpiKey);
            if (targetDefaultIdx !== -1) {
                const currentIdx = window.financeCurrent.indexOf(kpiKey);
                if (currentIdx !== -1) {
                    window.financeCurrent.splice(currentIdx, 1);
                    let insertIdx = window.financeCurrent.length;
                    for (let i = 0; i < window.financeCurrent.length; i++) {
                        const itemKey = window.financeCurrent[i];
                        if (defaultOrder.indexOf(itemKey) > targetDefaultIdx) {
                            insertIdx = i;
                            break;
                        }
                    }
                    window.financeCurrent.splice(insertIdx, 0, kpiKey);

                    const container = document.getElementById('finance-kpi-container');
                    if (container) {
                        const wrappers = {};
                        container.querySelectorAll('[data-kpi-wrapper]').forEach(w => { wrappers[w.dataset.kpiWrapper] = w; });
                        window.financeCurrent.forEach(key => { if (wrappers[key]) container.appendChild(wrappers[key]); });
                    }
                }
            }

            updateFinanceStyleDisplays();
            showFinanceToast('✓ Kartu ' + kpiKey + ' dikembalikan ke default.');
        }
    </script>
</x-dashboard-layout>
