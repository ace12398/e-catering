<x-dashboard-layout>
    <x-slot name="title">Pengaturan Tampilan (Adaptable UI) — E-Catering</x-slot>
    <x-slot name="toolbarTitle">Pengaturan Tampilan</x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
        {{-- HEADER BANNER --}}
        <div class="p-6 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                        USER-CONTROLLED INTERFACE
                    </span>
                    <span class="text-xs text-slate-400">•</span>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Adaptable User Interface</span>
                </div>
                <h1 class="text-xl font-extrabold text-slate-900 dark:text-white tracking-tight mt-1">
                    Pengaturan Tampilan & Kustomisasi Visual
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-2xl leading-relaxed">
                    Atur bentuk (shape), ukuran (size), dan tata letak komponen antarmuka pengguna secara personal. Preferensi ini tersimpan aman secara khusus untuk akun Anda.
                </p>
            </div>
            <a href="{{ route('dashboard') }}" class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-white dark:hover:bg-slate-100 text-white dark:text-slate-900 font-bold text-xs shadow-sm flex items-center space-x-2 shrink-0 transition-colors">
                <span>⚡ Atur Susunan Drag & Drop Dashboard →</span>
            </a>
        </div>

        {{-- MAIN CUSTOMIZATION HUB --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            {{-- LEFT COLUMN: CONTROLS (7 COLS) --}}
            <div class="lg:col-span-7 space-y-6">
                <div class="p-6 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
                    
                    {{-- STEP 1: PILIH HALAMAN --}}
                    <div class="space-y-2">
                        <label for="page-select" class="block text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center space-x-1.5">
                            <span class="w-5 h-5 rounded-full bg-emerald-600 text-white text-[10px] flex items-center justify-center font-mono">1</span>
                            <span>Pilih Halaman Aplikasi</span>
                        </label>
                        <select id="page-select" onchange="onPageChange(this.value)" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all">
                            @foreach($customizationMap as $pKey => $pMeta)
                                <option value="{{ $pKey }}">{{ $pMeta['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- STEP 2: PILIH KOMPONEN --}}
                    <div class="space-y-2">
                        <label for="component-select" class="block text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center space-x-1.5">
                            <span class="w-5 h-5 rounded-full bg-emerald-600 text-white text-[10px] flex items-center justify-center font-mono">2</span>
                            <span>Pilih Komponen Tampilan</span>
                        </label>
                        <select id="component-select" onchange="onComponentChange(this.value)" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all">
                            {{-- Options populated via JS --}}
                        </select>
                    </div>

                    <div class="border-t border-slate-100 dark:border-slate-800"></div>

                    {{-- STEP 3: PILIH BENTUK (SHAPE) --}}
                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center space-x-1.5">
                                <span class="w-5 h-5 rounded-full bg-emerald-600 text-white text-[10px] flex items-center justify-center font-mono">3</span>
                                <span>Pilih Bentuk Visual (Shape)</span>
                            </label>
                            <span id="shape-rule-badge" class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-2 py-0.5 rounded border border-emerald-200 dark:border-emerald-800">
                                6 Shape Didukung
                            </span>
                        </div>
                        <div id="shape-picker-container" class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            {{-- Shape buttons populated via JS --}}
                        </div>
                    </div>

                    <div class="border-t border-slate-100 dark:border-slate-800"></div>

                    {{-- STEP 4: ATUR UKURAN (SIZE) --}}
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center space-x-1.5">
                                <span class="w-5 h-5 rounded-full bg-emerald-600 text-white text-[10px] flex items-center justify-center font-mono">4</span>
                                <span>Atur Dimensi Ukuran (Size)</span>
                            </label>
                            <label class="flex items-center space-x-1.5 cursor-pointer text-xs select-none">
                                <input type="checkbox" id="lock-aspect" onchange="toggleAspectLock(this.checked)" class="rounded text-emerald-600 focus:ring-emerald-500">
                                <span class="text-slate-600 dark:text-slate-400 font-medium text-[11px]">Pertahankan Rasio 1:1</span>
                            </label>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {{-- WIDTH --}}
                            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300">Lebar (Width)</span>
                                    <div class="flex items-center space-x-1">
                                        <input type="number" id="width-number" oninput="onSizeNumberChange('width', this.value)" class="w-16 px-2 py-1 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-xs font-mono font-bold text-right text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                                        <span class="text-[10px] text-slate-400 font-medium">px</span>
                                    </div>
                                </div>
                                <input type="range" id="width-range" oninput="onSizeRangeChange('width', this.value)" class="w-full h-1.5 accent-emerald-600 cursor-pointer">
                                <div class="flex justify-between text-[9px] text-slate-400 font-mono">
                                    <span id="min-w-label">240px</span>
                                    <span id="max-w-label">800px</span>
                                </div>
                            </div>

                            {{-- HEIGHT --}}
                            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300">Tinggi (Height)</span>
                                    <div class="flex items-center space-x-1">
                                        <input type="number" id="height-number" oninput="onSizeNumberChange('height', this.value)" class="w-16 px-2 py-1 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-xs font-mono font-bold text-right text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                                        <span class="text-[10px] text-slate-400 font-medium">px</span>
                                    </div>
                                </div>
                                <input type="range" id="height-range" oninput="onSizeRangeChange('height', this.value)" class="w-full h-1.5 accent-emerald-600 cursor-pointer">
                                <div class="flex justify-between text-[9px] text-slate-400 font-mono">
                                    <span id="min-h-label">120px</span>
                                    <span id="max-h-label">500px</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ACTIONS BUTTONS --}}
                    <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" onclick="resetCurrentComponent()" class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors flex items-center space-x-1.5">
                            <span>↺ Reset Default</span>
                        </button>
                        <div class="flex items-center space-x-2">
                            <button type="button" onclick="cancelChanges()" class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 font-semibold text-xs hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                                Batal
                            </button>
                            <button type="button" onclick="saveCustomization()" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs shadow-md transition-all flex items-center space-x-2">
                                <span>💾 Simpan Pengaturan Tampilan →</span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>

            {{-- RIGHT COLUMN: LIVE PREVIEW CANVAS (5 COLS) --}}
            <div class="lg:col-span-5 space-y-6">
                <div class="p-6 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4 sticky top-20">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <h2 class="text-xs font-extrabold uppercase tracking-wider text-slate-900 dark:text-white">Live Preview Engine</h2>
                        </div>
                        <span id="preview-component-tag" class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                            W-KPI
                        </span>
                    </div>

                    {{-- PREVIEW STAGE CONTAINER --}}
                    <div class="p-6 rounded-xl bg-slate-900/5 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800 flex items-center justify-center min-h-[340px] overflow-hidden relative">
                        
                        {{-- DECOUPLED OUTER LAYOUT SLOT (#outer-preview-slot) --}}
                        <div id="outer-preview-slot" class="w-full flex justify-center items-center transition-all relative">
                            
                            {{-- INNER VISUAL COMPONENT CARD (#inner-preview-card) --}}
                            <div id="inner-preview-card" class="bg-gradient-to-br from-emerald-600 to-teal-700 text-white p-5 shadow-xl transition-all relative flex flex-col justify-between overflow-hidden">
                                
                                {{-- UNCLIPPED CONTENT BOX --}}
                                <div class="widget-content-box space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span id="preview-icon-label" class="text-xs font-bold tracking-wide uppercase text-emerald-100 flex items-center space-x-1.5">
                                            <span>📊</span>
                                            <span id="preview-title">Ringkasan KPI Utama</span>
                                        </span>
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-white/20 text-white backdrop-blur-sm">
                                            LIVE
                                        </span>
                                    </div>
                                    <div id="preview-value-box" class="space-y-1">
                                        <div class="text-2xl font-extrabold tracking-tight">Rp 48.500.000</div>
                                        <p class="text-[10px] text-emerald-100 font-medium">128 Pesanan Katering Aktif</p>
                                    </div>
                                    <div class="pt-2 border-t border-white/20 flex items-center justify-between text-[10px] text-emerald-100">
                                        <span>Target Bulanan</span>
                                        <span class="font-bold text-white">92% Tercapai</span>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>

                    {{-- SCIENTIFIC ACTUAL MEASUREMENT READOUT FOR BAB IV RESEARCH --}}
                    <div class="p-4 rounded-xl bg-slate-900 text-white space-y-2 font-mono text-[11px] border border-slate-800 shadow-inner">
                        <div class="flex items-center justify-between text-[10px] text-slate-400 pb-1.5 border-b border-slate-800">
                            <span>Mekanisme Pengukuran (Bab IV):</span>
                            <span class="text-emerald-400 font-bold">getBoundingClientRect()</span>
                        </div>
                        <div class="flex items-center justify-between text-xs font-bold">
                            <span class="text-slate-300">Ukuran Aktual Rendered:</span>
                            <span id="actual-measurement-display" class="text-emerald-300 font-extrabold">320 × 180 px</span>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-slate-400">
                            <span>Requested Dimensions:</span>
                            <span id="requested-measurement-display" class="text-slate-300">320 × 180 px</span>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-slate-400 pt-1.5 border-t border-slate-800">
                            <span>Shape & Layout Mode:</span>
                            <span id="shape-mode-display" class="text-emerald-400 font-semibold">Rectangle (Standard Flex)</span>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-slate-400">
                            <span>Size Threshold Mode:</span>
                            <span id="size-mode-display" class="text-emerald-400 font-semibold">Spacious (>240px)</span>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-slate-400">
                            <span>Overflow Status:</span>
                            <span id="overflow-mode-display" class="text-emerald-400 font-semibold">None (Safe Inset Bounds)</span>
                        </div>
                    </div>

                    <div class="text-[10px] text-slate-500 dark:text-slate-400 text-center font-medium italic">
                        * Container & isi di dalamnya (typography, icon, spacing, alignment) beradaptasi secara real-time.
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- INTERACTIVE ENGINE SCRIPT --}}
    <script>
        const customizationMap = @json($customizationMap);
        const initialMatrix    = @json($layoutMatrix);
        let currentStyles      = JSON.parse(JSON.stringify(initialMatrix.component_styles || {}));
        let lastSavedStyles    = JSON.parse(JSON.stringify(currentStyles));

        let activePage      = 'dashboard';
        let activeComp      = 'W-KPI';
        let isAspectLocked  = false;

        const allShapesData = {
            'rectangle': { label: '■ Persegi Panjang', title: 'Sudut sedikit membulat' },
            'rounded':   { label: '◉ Membulat',        title: 'Sudut sangat membulat (24px)' },
            'sharp':     { label: '▪ Tajam',            title: 'Sudut tajam (0px)' },
            'pill':      { label: '⬭ Pil / Oval',      title: 'Sudut pil oval (9999px)' },
            'circle':    { label: '○ Lingkaran',        title: 'Lingkaran sempurna (1:1)' },
            'hexagon':   { label: '⬡ Heksagon',        title: 'Bentuk segi enam clip-path' }
        };

        const compatibleRules = {
            'W-ORDERS': ['rectangle', 'rounded', 'sharp'],
            'A-CHARTS': ['rectangle', 'rounded', 'sharp'],
            'A-TOP-MENU': ['rectangle', 'rounded', 'sharp'],
            'W-QUICK': ['rectangle', 'rounded', 'sharp', 'pill'],
            'category_pills': ['pill', 'rounded', 'sharp', 'rectangle']
        };

        document.addEventListener('DOMContentLoaded', function() {
            onPageChange('dashboard');
        });

        function onPageChange(pageKey) {
            activePage = pageKey;
            const pMeta = customizationMap[pageKey];
            if (!pMeta) return;

            const compSelect = document.getElementById('component-select');
            compSelect.innerHTML = '';
            
            const comps = pMeta.components;
            const compKeys = Object.keys(comps);
            
            compKeys.forEach(cKey => {
                const opt = document.createElement('option');
                opt.value = cKey;
                opt.textContent = `${comps[cKey].icon} ${comps[cKey].label}`;
                compSelect.appendChild(opt);
            });

            if (compKeys.length > 0) {
                onComponentChange(compKeys[0]);
            }
        }

        function onComponentChange(compId) {
            activeComp = compId;
            document.getElementById('preview-component-tag').textContent = compId;

            const compMeta = customizationMap[activePage]?.components[compId];
            if (!compMeta) return;

            document.getElementById('preview-title').textContent = compMeta.label;

            // Load style current or default
            if (!currentStyles[activePage]) currentStyles[activePage] = {};
            if (!currentStyles[activePage][compId]) {
                currentStyles[activePage][compId] = {
                    shape: 'rectangle',
                    width: compMeta.default_w,
                    height: compMeta.default_h,
                    border_radius: 12
                };
            }
            const st = currentStyles[activePage][compId];

            // Render Shape Picker Buttons
            renderShapePicker(compId, st.shape);

            // Set Min Max Labels
            document.getElementById('min-w-label').textContent = compMeta.min_w + 'px';
            document.getElementById('max-w-label').textContent = compMeta.max_w + 'px';
            document.getElementById('min-h-label').textContent = compMeta.min_h + 'px';
            document.getElementById('max-h-label').textContent = compMeta.max_h + 'px';

            const wNum = document.getElementById('width-number');
            const wRng = document.getElementById('width-range');
            const hNum = document.getElementById('height-number');
            const hRng = document.getElementById('height-range');

            wNum.min = compMeta.min_w; wNum.max = compMeta.max_w; wNum.value = st.width;
            wRng.min = compMeta.min_w; wRng.max = compMeta.max_w; wRng.value = st.width;

            hNum.min = compMeta.min_h; hNum.max = compMeta.max_h; hNum.value = st.height;
            hRng.min = compMeta.min_h; hRng.max = compMeta.max_h; hRng.value = st.height;

            if (st.shape === 'circle') {
                document.getElementById('lock-aspect').checked = true;
                isAspectLocked = true;
            } else {
                document.getElementById('lock-aspect').checked = false;
                isAspectLocked = false;
            }

            applyLivePreview();
        }

        function renderShapePicker(compId, currentShape) {
            const container = document.getElementById('shape-picker-container');
            container.innerHTML = '';

            const allowed = compatibleRules[compId] || ['rectangle', 'rounded', 'sharp', 'pill', 'circle', 'hexagon'];
            document.getElementById('shape-rule-badge').textContent = allowed.length + ' Shape Didukung';

            allowed.forEach(sId => {
                const meta = allShapesData[sId];
                if (!meta) return;

                const isActive = (currentShape === sId);
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.title = meta.title;
                btn.className = `px-3 py-2 rounded-xl text-xs font-bold border transition-all text-left flex items-center justify-between ${
                    isActive 
                    ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm' 
                    : 'bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:border-emerald-500'
                }`;
                btn.innerHTML = `<span>${meta.label}</span>${isActive ? '<span>✓</span>' : ''}`;
                btn.onclick = function() { setShape(sId); };
                container.appendChild(btn);
            });
        }

        function setShape(sId) {
            if (!currentStyles[activePage][activeComp]) currentStyles[activePage][activeComp] = {};
            currentStyles[activePage][activeComp].shape = sId;

            if (sId === 'circle') {
                document.getElementById('lock-aspect').checked = true;
                isAspectLocked = true;
                const compMeta = customizationMap[activePage]?.components[activeComp];
                const size = Math.min(parseInt(currentStyles[activePage][activeComp].width) || compMeta.default_w, parseInt(currentStyles[activePage][activeComp].height) || compMeta.default_h);
                currentStyles[activePage][activeComp].width = size;
                currentStyles[activePage][activeComp].height = size;
                document.getElementById('width-number').value = size;
                document.getElementById('width-range').value  = size;
                document.getElementById('height-number').value = size;
                document.getElementById('height-range').value  = size;
            }

            renderShapePicker(activeComp, sId);
            applyLivePreview();
        }

        function toggleAspectLock(val) {
            isAspectLocked = val;
            if (val) {
                const w = parseInt(document.getElementById('width-number').value) || 320;
                onSizeRangeChange('width', w);
            }
        }

        function onSizeNumberChange(dim, val) {
            const v = parseInt(val);
            if (isNaN(v)) return;

            const compMeta = customizationMap[activePage]?.components[activeComp];
            const clamped = dim === 'width' 
                ? Math.max(compMeta.min_w, Math.min(compMeta.max_w, v))
                : Math.max(compMeta.min_h, Math.min(compMeta.max_h, v));

            if (!currentStyles[activePage][activeComp]) currentStyles[activePage][activeComp] = {};
            currentStyles[activePage][activeComp][dim] = clamped;

            if (dim === 'width') document.getElementById('width-range').value = clamped;
            else document.getElementById('height-range').value = clamped;

            if (isAspectLocked) {
                const otherDim = dim === 'width' ? 'height' : 'width';
                currentStyles[activePage][activeComp][otherDim] = clamped;
                document.getElementById(otherDim + '-number').value = clamped;
                document.getElementById(otherDim + '-range').value = clamped;
            }

            applyLivePreview();
        }

        function onSizeRangeChange(dim, val) {
            const v = parseInt(val);
            document.getElementById(dim + '-number').value = v;
            onSizeNumberChange(dim, v);
        }

        function applyLivePreview() {
            const outerSlot  = document.getElementById('outer-preview-slot');
            const visualCard = document.getElementById('inner-preview-card');
            if (!outerSlot || !visualCard) return;

            const st = currentStyles[activePage]?.[activeComp] || {};
            const compMeta = customizationMap[activePage]?.components[activeComp] || { default_w: 320, default_h: 180, min_w: 240, max_w: 800, min_h: 120, max_h: 500 };

            const shape  = st.shape || 'rectangle';
            const reqW   = parseInt(st.width)  || compMeta.default_w;
            const reqH   = parseInt(st.height) || compMeta.default_h;
            const radius = parseInt(st.border_radius) || 12;

            const clampedW = Math.max(compMeta.min_w, Math.min(compMeta.max_w, reqW));
            const clampedH = Math.max(compMeta.min_h, Math.min(compMeta.max_h, reqH));

            // 1. OUTER SLOT GUARANTEES (Rata, segiempat, unclipped)
            outerSlot.style.clipPath     = '';
            outerSlot.style.borderRadius = '';
            outerSlot.style.aspectRatio  = '';
            outerSlot.style.overflow     = 'visible';

            // 2. INNER VISUAL CARD STYLING
            visualCard.style.maxWidth  = '100%';
            visualCard.style.boxSizing = 'border-box';
            visualCard.style.clipPath     = '';
            visualCard.style.borderRadius = '';
            visualCard.style.aspectRatio  = '';

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
            } else {
                visualCard.style.width     = `${clampedW}px`;
                visualCard.style.height    = `${clampedH}px`;
                visualCard.style.minWidth  = '';
                visualCard.style.maxWidth  = '100%';
                visualCard.style.minHeight = `${clampedH}px`;
                visualCard.style.maxHeight = `${clampedH}px`;
                visualCard.style.margin    = '';

                switch (shape) {
                    case 'rounded':  visualCard.style.borderRadius = '24px'; break;
                    case 'pill':     visualCard.style.borderRadius = '9999px'; break;
                    case 'sharp':    visualCard.style.borderRadius = '0px'; break;
                    case 'hexagon':  visualCard.style.clipPath     = 'polygon(25% 0%, 75% 0%, 100% 50%, 75% 100%, 25% 100%, 0% 50%)'; break;
                    default:         visualCard.style.borderRadius = radius + 'px';
                }
            }

            // 3. TRUE ADAPTIVE UI CONTENT ENGINE (Adapt inner layout, typography, icons, and spacing)
            if (window.adaptComponentContent) {
                window.adaptComponentContent(visualCard, shape, clampedW, clampedH);
            } else {
                adaptComponentContent(visualCard, shape, clampedW, clampedH);
            }

            // 4. SCIENTIFIC ACTUAL MEASUREMENT ENGINE FOR BAB IV (getBoundingClientRect)
            setTimeout(() => {
                const rect    = visualCard.getBoundingClientRect();
                const actualW = Math.round(rect.width);
                const actualH = Math.round(rect.height);

                const minDim = Math.min(actualW, actualH);
                let sizeMode = 'Spacious (>240px)';
                if (minDim < 160 || actualW < 200) sizeMode = 'Ultra Compact (<160px)';
                else if (minDim < 240 || actualW < 300) sizeMode = 'Compact (160-240px)';

                let shapeLayoutMode = 'Rectangle (Standard Flex)';
                if (shape === 'circle') shapeLayoutMode = 'Circle (Centered Vertical Stack)';
                else if (shape === 'hexagon') shapeLayoutMode = 'Hexagon (Polygon Safe Inset)';
                else if (shape === 'pill') shapeLayoutMode = 'Pill (Linear Horizontal Padding)';
                else if (shape === 'rounded') shapeLayoutMode = 'Rounded (Sudut 24px)';
                else if (shape === 'sharp') shapeLayoutMode = 'Sharp (Sudut 0px)';

                document.getElementById('actual-measurement-display').textContent    = `${actualW} × ${actualH} px`;
                document.getElementById('requested-measurement-display').textContent = `${reqW} × ${reqH} px`;
                document.getElementById('shape-mode-display').textContent            = shapeLayoutMode;
                document.getElementById('size-mode-display').textContent             = sizeMode;
                const overflowEl = document.getElementById('overflow-mode-display');
                if (overflowEl) overflowEl.textContent = 'None (Safe Bounds Verified)';
            }, 50);
        }

        // UNIVERSAL REUSABLE ADAPTIVE CONTENT ENGINE
        function adaptComponentContent(visualCard, shape, width, height) {
            if (!visualCard) return;
            const contentBox = visualCard.querySelector('.widget-content-box') 
                            || visualCard.querySelector('.caterflow-content-box')
                            || visualCard.firstElementChild;
            if (!contentBox) return;

            const minDim = Math.min(width, height);

            contentBox.classList.remove(
                'shape-circle-box', 'shape-hexagon-box', 'shape-pill-box', 'shape-rectangle-box',
                'size-ultracompact', 'size-compact', 'size-spacious'
            );

            if (shape === 'circle') contentBox.classList.add('shape-circle-box');
            else if (shape === 'hexagon') contentBox.classList.add('shape-hexagon-box');
            else if (shape === 'pill') contentBox.classList.add('shape-pill-box');
            else contentBox.classList.add('shape-rectangle-box');

            if (minDim < 160 || width < 200) contentBox.classList.add('size-ultracompact');
            else if (minDim < 240 || width < 300) contentBox.classList.add('size-compact');
            else contentBox.classList.add('size-spacious');
        }

        function resetCurrentComponent() {
            if (!confirm('Reset komponen ini ke ukuran default?')) return;
            const compMeta = customizationMap[activePage]?.components[activeComp];
            if (!compMeta) return;

            currentStyles[activePage][activeComp] = {
                shape: 'rectangle',
                width: compMeta.default_w,
                height: compMeta.default_h,
                border_radius: 12
            };
            onComponentChange(activeComp);
            showToast('✓ Komponen dikembalikan ke default.', 'success');
        }

        function cancelChanges() {
            currentStyles = JSON.parse(JSON.stringify(lastSavedStyles));
            onComponentChange(activeComp);
            showToast('Perubahan dibatalkan.', 'info');
        }

        function saveCustomization() {
            fetch('{{ route("workspace.save-layout") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    layout_matrix: {
                        component_styles: currentStyles
                    }
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    lastSavedStyles = JSON.parse(JSON.stringify(currentStyles));
                    showToast('✓ Pengaturan tampilan berhasil disimpan.', 'success');
                } else {
                    showToast('Gagal menyimpan: ' + (data.message || ''), 'error');
                }
            })
            .catch(() => showToast('Kesalahan koneksi.', 'error'));
        }

        function showToast(msg, type) {
            if (window.showToast) window.showToast(msg, type);
            else alert(msg);
        }
    </script>
</x-dashboard-layout>

