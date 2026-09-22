<x-dashboard-layout>
    <x-slot name="title">Katalog Menu Katering — E-Catering</x-slot>
    <x-slot name="toolbarTitle">Katalog Menu Katering</x-slot>

    {{-- SortableJS untuk Category Toolbar & Menu Grid Reorder --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

    @php
        $effectiveMatrix = $layout_matrix ?? auth()->user()?->preference?->effective_layout_matrix ?? \App\Models\WorkspacePreference::getDefaultLayoutMatrix();
        $menusSavedStyles = $effectiveMatrix['component_styles']['menus'] ?? [];
        $defaultMenusStyles = [
            'category_pills' => ['shape' => 'pill', 'width' => 200, 'height' => 44, 'border_radius' => 9999],
            'M-HEADER'       => ['shape' => 'rectangle', 'width' => 900, 'height' => 120, 'border_radius' => 16],
            'M-TOOLBAR'      => ['shape' => 'rectangle', 'width' => 900, 'height' => 80,  'border_radius' => 16],
            'M-GRID'         => ['shape' => 'rectangle', 'width' => 900, 'height' => 450, 'border_radius' => 16],
        ];
        $menuSectionsOrder   = $menuSectionsOrder ?? $effectiveMatrix['menu_sections_order'] ?? ['M-HEADER', 'M-TOOLBAR', 'M-GRID'];
        $menuCategoriesOrder = $menuCategoriesOrder ?? $effectiveMatrix['menu_categories_order'] ?? [];
        $menuItemsOrder      = $menuItemsOrder ?? $effectiveMatrix['menu_items_order'] ?? [];
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

    <div class="max-w-7xl mx-auto space-y-6 relative" id="menus-root">
        {{-- TOAST NOTIFICATION --}}
        <div id="menus-toast" class="hidden fixed bottom-6 right-6 z-50 p-4 rounded-xl bg-slate-900 text-white shadow-2xl border border-slate-700 flex items-center space-x-3 transition-all transform translate-y-4 opacity-0">
            <span id="menus-toast-icon" class="text-amber-400 text-lg font-bold">✓</span>
            <span id="menus-toast-message" class="text-xs font-medium">Layout katalog menu berhasil diperbarui.</span>
        </div>

        {{-- CANONICAL ADAPTIVE MODE BANNER (MATCHING /finance & /dashboard) --}}
        <div id="menus-edit-bar" class="hidden sticky top-4 z-40 p-4 rounded-2xl bg-amber-950/90 dark:bg-amber-950/95 backdrop-blur border-2 border-amber-500 text-white shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center space-x-3">
                <span class="p-2.5 rounded-xl bg-amber-500/20 border border-amber-400/30 text-amber-300 font-mono text-base">✨</span>
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-300">Mode Penyesuaian Tampilan Aktif — Katalog Menu</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500 text-slate-950">Drag & Drop Aktif</span>
                    </div>
                    <p class="text-xs text-amber-100/90 mt-0.5">Ubah bentuk (shape), lebar, tinggi, dan geser urutan kartu menu atau toolbar kategori secara live.</p>
                </div>
            </div>

            {{-- Shape selector for category toolbar pills --}}
            <div class="flex items-center space-x-2 bg-slate-900/90 p-2 rounded-xl border border-slate-700/80">
                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-400">Bentuk Pill:</span>
                @php
                    $mShapes = [
                        'pill'      => '⬭ Pil',
                        'rounded'   => '◉ Bulat',
                        'sharp'     => '▪ Tajam',
                        'rectangle' => '■ Kotak',
                    ];
                @endphp
                @foreach($mShapes as $msId => $msLabel)
                    <button type="button" data-shape="{{ $msId }}"
                        onclick="setMenuPillShape('{{ $msId }}')"
                        class="m-shape-btn px-2.5 py-1 rounded-lg text-[10px] font-semibold border transition-colors bg-white text-slate-800 border-slate-300 hover:bg-amber-500 hover:text-white"
                    >{{ $msLabel }}</button>
                @endforeach
            </div>

            <div class="flex items-center space-x-2 shrink-0">
                <button type="button" onclick="cancelMenusLayout()" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs transition-colors border border-slate-600">✕ Batal</button>
                <button type="button" onclick="resetMenusLayout()" class="px-3.5 py-2 rounded-xl bg-amber-900/80 hover:bg-amber-800 text-amber-200 font-semibold text-xs transition-colors border border-amber-600">🔄 Reset Default</button>
                <button type="button" onclick="saveMenusLayout()" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold text-xs shadow-md transition-colors flex items-center space-x-1.5"><span>💾 Simpan Tampilan</span></button>
            </div>
        </div>

        {{-- MAIN SECTIONS CONTAINER (WORKSPACE SECTIONS REORDERABLE) --}}
        <div id="menus-sections-container" class="space-y-6">
            @foreach($menuSectionsOrder as $sectionId)
                @if($sectionId === 'M-HEADER')
                    {{-- 1. HEADER SEARCH & FILTERS SECTION (Component ID: M-HEADER) --}}
                    <div data-menu-section="M-HEADER" data-widget-wrapper="M-HEADER" class="w-full flex flex-col space-y-3 transition-all relative">
                        {{-- Dedicated Section Drag Handle --}}
                        <div class="menu-section-drag-handle hidden self-start px-2.5 py-1 rounded-lg bg-amber-500/20 hover:bg-amber-500 text-amber-800 dark:text-amber-200 hover:text-white border border-amber-400/40 cursor-grab active:cursor-grabbing font-mono text-xs font-bold transition-colors select-none shadow-sm mb-1">
                            ⠿ GESER BAGIAN: Header & Pencarian
                        </div>

                        {{-- VISUAL CARD --}}
                        <div id="menus-widget-card-M-HEADER" data-component-id="M-HEADER" class="caterflow-visual-card w-full p-6 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4 overflow-hidden transition-all">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                <div>
                                    <h2 class="text-xl font-bold text-slate-900 dark:text-white stat-value">Katalog Paket Menu Katering</h2>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 subtext">Pilihan paket menu hidangan katering dengan jaminan mutu dan kualitas terbaik.</p>
                                </div>

                                <div class="flex items-center space-x-2 shrink-0">
                                    <!-- Search Input Form -->
                                    <form method="GET" action="{{ route('menus.index') }}" class="flex items-center space-x-2">
                                        @if($selectedCategory)
                                            <input type="hidden" name="category" value="{{ $selectedCategory }}">
                                        @endif
                                        <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama hidangan..."
                                               class="px-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:border-amber-500 w-52">
                                        <button type="submit" class="px-4 py-2 rounded-xl bg-amber-600 text-white font-semibold text-xs hover:bg-amber-500 transition-colors shadow-sm">Cari</button>
                                    </form>
                                    {{-- TOMBOL SESUAIKAN TAMPILAN --}}
                                    <button type="button" id="menus-btn-edit" onclick="toggleMenusEditMode()"
                                            class="h-9 px-4 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs transition-colors flex items-center space-x-2 shadow-sm shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        <span id="menus-btn-text">⚙️ Sesuaikan Tampilan</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- SEPARATE RECTANGULAR CONFIGURATION EDITOR (OUTSIDE PREVIEW) --}}
                        <div class="menus-edit-controls hidden w-full p-4 rounded-xl bg-slate-900/90 text-white border border-slate-700/60 shadow-lg space-y-3 text-xs backdrop-blur z-20">
                            <div class="flex items-center justify-between font-bold border-b border-slate-700 pb-2 text-xs">
                                <span class="text-amber-400 font-bold">Atur Komponen: Header Katalog & Pencarian (M-HEADER)</span>
                                <button type="button" onclick="resetSingleMenusComponent('M-HEADER')" class="px-2 py-1 rounded text-xs font-semibold bg-amber-800/80 hover:bg-amber-700 text-white border border-amber-600 transition-colors">🔄 Reset</button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1.5">Bentuk</span>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach(['rectangle'=>'■ Kotak','rounded'=>'◉ Bulat','sharp'=>'▪ Tajam','pill'=>'⬭ Pil'] as $sId => $sLabel)
                                            <button type="button" onclick="setMenusComponentShape('M-HEADER', '{{ $sId }}')" class="m-shape-btn-M-HEADER px-2.5 py-1.5 rounded-lg text-xs font-semibold border bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-600 text-slate-800 dark:text-slate-200 hover:bg-amber-500 hover:text-white transition-colors" data-shape="{{ $sId }}">{{ $sLabel }}</button>
                                        @endforeach
                                    </div>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                        <span>Lebar</span>
                                        <span id="m-wval-M-HEADER" class="text-amber-400 font-mono font-bold">900px</span>
                                    </div>
                                    <input type="range" min="240" max="900" step="10" value="900" id="m-width-slider-M-HEADER" oninput="onMenusSliderInput('M-HEADER', 'width', this.value)" class="w-full accent-amber-500">
                                </div>
                                <div>
                                    <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                        <span>Tinggi</span>
                                        <span id="m-hval-M-HEADER" class="text-amber-400 font-mono font-bold">120px</span>
                                    </div>
                                    <input type="range" min="80" max="400" step="10" value="120" id="m-height-slider-M-HEADER" oninput="onMenusSliderInput('M-HEADER', 'height', this.value)" class="w-full accent-amber-500">
                                </div>
                            </div>
                            <div class="text-[11px] font-mono text-amber-400 pt-1.5 border-t border-slate-700/60" id="m-size-display-M-HEADER">900 × 120 px</div>
                        </div>
                    </div>

                @elseif($sectionId === 'M-TOOLBAR')
                    {{-- 2. CATEGORY TOOLBAR SECTION (Component ID: M-TOOLBAR) --}}
                    <div data-menu-section="M-TOOLBAR" data-widget-wrapper="M-TOOLBAR" class="w-full flex flex-col space-y-3 transition-all relative">
                        {{-- Dedicated Section Drag Handle --}}
                        <div class="menu-section-drag-handle hidden self-start px-2.5 py-1 rounded-lg bg-amber-500/20 hover:bg-amber-500 text-amber-800 dark:text-amber-200 hover:text-white border border-amber-400/40 cursor-grab active:cursor-grabbing font-mono text-xs font-bold transition-colors select-none shadow-sm mb-1">
                            ⠿ GESER BAGIAN: Toolbar Kategori
                        </div>

                        {{-- VISUAL CARD --}}
                        <div id="menus-widget-card-M-TOOLBAR" data-component-id="M-TOOLBAR" class="caterflow-visual-card w-full p-4 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden transition-all">
                            <div class="flex items-center space-x-2 overflow-x-auto text-xs py-1">
                                {{-- Semua Kategori pill (FIXED) --}}
                                <a href="{{ route('menus.index') }}"
                                   class="px-3.5 py-1.5 rounded-full font-semibold transition-all shrink-0 {{ !$selectedCategory ? 'bg-amber-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200' }}">
                                    Semua Kategori
                                </a>
                                {{-- Sortable Category Pills Container --}}
                                <div id="menus-category-toolbar" class="flex items-center space-x-2">
                                    @foreach($categories as $cat)
                                        <div data-cat-slug="{{ $cat->slug }}" class="menu-category-pill shrink-0 flex items-center">
                                            <span class="menus-cat-drag-handle hidden mr-1 px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-500 dark:text-amber-300 border border-amber-400/40 cursor-grab active:cursor-grabbing font-mono font-bold text-xs select-none shadow-sm" title="Tahan dan geser untuk memindahkan urutan kategori">⠿</span>
                                            <a href="{{ route('menus.index', ['category' => $cat->slug]) }}"
                                               class="px-3.5 py-1.5 rounded-full font-semibold transition-all {{ $selectedCategory === $cat->slug ? 'bg-amber-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                                                <span>{{ $cat->icon }}</span>
                                                <span class="ml-1">{{ $cat->name }}</span>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- SEPARATE RECTANGULAR CONFIGURATION EDITOR (OUTSIDE PREVIEW) --}}
                        <div class="menus-edit-controls hidden w-full p-4 rounded-xl bg-slate-900/90 text-white border border-slate-700/60 shadow-lg space-y-3 text-xs backdrop-blur z-20">
                            <div class="flex items-center justify-between font-bold border-b border-slate-700 pb-2 text-xs">
                                <span class="text-amber-400 font-bold">Atur Komponen: Kategori Toolbar (M-TOOLBAR)</span>
                                <button type="button" onclick="resetSingleMenusComponent('M-TOOLBAR')" class="px-2 py-1 rounded text-xs font-semibold bg-amber-800/80 hover:bg-amber-700 text-white border border-amber-600 transition-colors">🔄 Reset</button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1.5">Bentuk</span>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach(['rectangle'=>'■ Kotak','rounded'=>'◉ Bulat','sharp'=>'▪ Tajam','pill'=>'⬭ Pil'] as $sId => $sLabel)
                                            <button type="button" onclick="setMenusComponentShape('M-TOOLBAR', '{{ $sId }}')" class="m-shape-btn-M-TOOLBAR px-2.5 py-1.5 rounded-lg text-xs font-semibold border bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-600 text-slate-800 dark:text-slate-200 hover:bg-amber-500 hover:text-white transition-colors" data-shape="{{ $sId }}">{{ $sLabel }}</button>
                                        @endforeach
                                    </div>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                        <span>Lebar</span>
                                        <span id="m-wval-M-TOOLBAR" class="text-amber-400 font-mono font-bold">900px</span>
                                    </div>
                                    <input type="range" min="240" max="900" step="10" value="900" id="m-width-slider-M-TOOLBAR" oninput="onMenusSliderInput('M-TOOLBAR', 'width', this.value)" class="w-full accent-amber-500">
                                </div>
                                <div>
                                    <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                        <span>Tinggi</span>
                                        <span id="m-hval-M-TOOLBAR" class="text-amber-400 font-mono font-bold">80px</span>
                                    </div>
                                    <input type="range" min="60" max="300" step="10" value="80" id="m-height-slider-M-TOOLBAR" oninput="onMenusSliderInput('M-TOOLBAR', 'height', this.value)" class="w-full accent-amber-500">
                                </div>
                            </div>
                            <div class="text-[11px] font-mono text-amber-400 pt-1.5 border-t border-slate-700/60" id="m-size-display-M-TOOLBAR">900 × 80 px</div>
                        </div>
                    </div>

                @elseif($sectionId === 'M-GRID')
                    {{-- 3. MENU CATALOG GRID SECTION (Component ID: M-GRID) --}}
                    <div data-menu-section="M-GRID" data-widget-wrapper="M-GRID" class="w-full flex flex-col space-y-3 transition-all relative">
                        {{-- Dedicated Section Drag Handle --}}
                        <div class="menu-section-drag-handle hidden self-start px-2.5 py-1 rounded-lg bg-amber-500/20 hover:bg-amber-500 text-amber-800 dark:text-amber-200 hover:text-white border border-amber-400/40 cursor-grab active:cursor-grabbing font-mono text-xs font-bold transition-colors select-none shadow-sm mb-1">
                            ⠿ GESER BAGIAN: Grid Menu Makanan
                        </div>

                        {{-- MENU CARDS GRID --}}
                        <div id="menus-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 items-start">
                            @forelse($menus as $menu)
                                @php $cId = 'menu_item_' . $menu->id; @endphp
                                <div data-menu-wrapper="{{ $menu->id }}" class="w-full flex flex-col items-center space-y-3 transition-all">
                                    
                                    {{-- BUSINESS PREVIEW CARD --}}
                                    <div id="menus-widget-card-{{ $cId }}" data-component-id="{{ $cId }}" class="caterflow-visual-card w-full p-5 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col justify-between hover:border-amber-500/50 transition-all duration-200 group h-full relative">
                                        
                                        {{-- Dedicated Card Drag Handle Badge (Edit Mode Only) --}}
                                        <div class="menu-card-drag-handle hidden absolute top-2 right-2 z-10 px-2 py-0.5 rounded-md bg-amber-500/20 hover:bg-amber-500 text-amber-800 dark:text-amber-200 hover:text-white border border-amber-400/40 cursor-grab active:cursor-grabbing transition-colors flex items-center space-x-1 select-none shadow-sm"
                                             title="Tahan dan geser untuk memindahkan urutan kartu menu">
                                            <svg class="w-3 h-3 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 8h16M4 16h16"></path>
                                            </svg>
                                            <span class="text-[9px] font-extrabold font-mono pointer-events-none">GESER</span>
                                        </div>

                                        <div class="space-y-3">
                                            <div class="flex items-center justify-between gap-2 flex-between-header">
                                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 adaptive-icon">
                                                    {{ $menu->category?->name ?? 'Katering' }}
                                                </span>
                                                @if($menu->is_halal)
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 adaptive-icon">
                                                        HALAL 🟢
                                                    </span>
                                                @endif
                                            </div>

                                            <h3 class="text-base font-bold text-slate-900 dark:text-white group-hover:text-amber-600 transition-colors leading-snug stat-value">
                                                {{ $menu->name }}
                                            </h3>

                                            <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed subtext desc-text">
                                                {{ $menu->description }}
                                            </p>

                                            <div class="flex items-center justify-between text-xs text-slate-400 pt-1 subtext secondary-info">
                                                <span>🔥 {{ $menu->calories }} kcal</span>
                                                <span>🏪 {{ $menu->vendor?->name ?? 'Dapur Utama' }}</span>
                                            </div>
                                        </div>

                                        <div class="pt-4 mt-3 bg-slate-50/50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
                                            <div>
                                                <span class="text-[10px] text-slate-400 font-semibold block uppercase subtext">Harga / Porsi</span>
                                                <span class="text-base font-extrabold text-slate-900 dark:text-white stat-value">
                                                    {{ format_idr($menu->price) }}
                                                </span>
                                            </div>

                                            <form method="POST" action="{{ route('cart.add') }}" class="shrink-0">
                                                @csrf
                                                <input type="hidden" name="menu_id" value="{{ $menu->id }}">
                                                <input type="hidden" name="quantity" value="1">
                                                <button type="submit" class="px-3.5 py-2 rounded-xl bg-amber-600 text-white font-semibold text-xs hover:bg-amber-500 transition-colors shadow-sm flex items-center space-x-1 btn-adaptive">
                                                    <span>🛒 + Keranjang</span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    {{-- SEPARATE RECTANGULAR CONFIGURATION EDITOR (OUTSIDE PREVIEW) --}}
                                    <div class="menus-edit-controls hidden w-full p-3.5 rounded-xl bg-slate-900/90 text-white border border-slate-700/60 shadow-lg space-y-2.5 text-xs backdrop-blur z-20">
                                        <div class="flex items-center justify-between font-bold border-b border-slate-700 pb-1.5 text-[10px]">
                                            <div class="flex items-center space-x-1.5">
                                                <span class="menu-card-drag-handle cursor-grab active:cursor-grabbing px-2 py-0.5 rounded bg-amber-600 hover:bg-amber-500 text-white text-[10px] font-bold flex items-center space-x-1 shadow-sm transition-colors select-none"
                                                      title="Tahan dan geser untuk memindahkan urutan kartu menu">
                                                    <span>⠿</span>
                                                    <span>Geser</span>
                                                </span>
                                                <span class="text-amber-400 font-bold">Atur: {{ Str::limit($menu->name, 12) }}</span>
                                            </div>
                                            <span class="text-[9px] text-slate-400 font-mono">(ID: {{ $menu->id }})</span>
                                        </div>
                                        <div class="space-y-1">
                                            <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Bentuk</span>
                                            <div class="grid grid-cols-3 gap-1">
                                                @foreach(['rectangle'=>'■ Kotak','rounded'=>'◉ Bulat','sharp'=>'▪ Tajam','pill'=>'⬭ Pil','circle'=>'○ Lingkar','hexagon'=>'⬡ Hex'] as $sId => $sLabel)
                                                    <button type="button" onclick="setMenusComponentShape('{{ $cId }}', '{{ $sId }}')" class="m-shape-btn-{{ $cId }} px-1 py-1 rounded text-[9px] font-semibold border text-center transition-colors bg-white text-slate-800 border-slate-300 hover:bg-amber-500 hover:text-white" data-shape="{{ $sId }}">{{ $sLabel }}</button>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2 gap-1.5">
                                            <div>
                                                <div class="flex items-center justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                                                    <span>Lebar</span>
                                                    <span id="m-wval-{{ $cId }}" class="text-amber-400 font-mono font-bold">300px</span>
                                                </div>
                                                <input type="range" min="180" max="500" step="10" value="300" id="m-width-slider-{{ $cId }}" oninput="onMenusSliderInput('{{ $cId }}', 'width', this.value)" class="w-full accent-amber-500">
                                            </div>
                                            <div>
                                                <div class="flex items-center justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                                                    <span>Tinggi</span>
                                                    <span id="m-hval-{{ $cId }}" class="text-amber-400 font-mono font-bold">260px</span>
                                                </div>
                                                <input type="range" min="140" max="450" step="10" value="260" id="m-height-slider-{{ $cId }}" oninput="onMenusSliderInput('{{ $cId }}', 'height', this.value)" class="w-full accent-amber-500">
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between text-[9px] font-mono font-semibold text-amber-400 pt-1 border-t border-slate-700">
                                            <span id="m-size-display-{{ $cId }}">300 × 260 px</span>
                                            <button type="button" onclick="resetSingleMenusComponent('{{ $cId }}')" class="px-1.5 py-0.5 rounded text-[9px] font-semibold bg-amber-800/80 hover:bg-amber-700 text-white border border-amber-600 transition-colors" title="Reset menu ini">🔄 Reset</button>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-full p-12 text-center rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 space-y-3">
                                    <div class="text-3xl">🍲</div>
                                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Menu Katering Tidak Ditemukan</h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Coba ubah kata kunci pencarian atau filter kategori.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <!-- PAGINATION -->
        <div class="pt-4">
            {{ $menus->links() }}
        </div>
    </div>

    {{-- ADAPTIVE ENGINE SCRIPT FOR MENUS CATALOG PAGE (CANONICAL MASTER /finance) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.menusIsEditMode        = false;
            window.menuSectionsOrder      = @json($menuSectionsOrder);
            window.menuSectionsLastSaved  = [...window.menuSectionsOrder];
            window.menusCategoryOrder     = @json($menuCategoriesOrder);
            window.menusCategoryLastSaved = [...window.menusCategoryOrder];
            window.menuItemsOrder         = @json($menuItemsOrder);
            window.menuItemsLastSaved     = [...window.menuItemsOrder];
            window.menusSavedStyles       = @json($menusSavedStyles);
            window.menusDefaultStyles     = @json($defaultMenusStyles);
            window.menusCurrentStyles     = Object.assign({}, window.menusDefaultStyles, window.menusSavedStyles);
            window.menusLastSavedStyles   = JSON.parse(JSON.stringify(window.menusCurrentStyles));
            window.menuSectionsSortable   = null;
            window.menusCatSortable       = null;
            window.menuCardsSortable      = null;

            initMenusSortables();
            initMenusComponentStyles();
        });

        function initMenusSortables() {
            // 1. Sortable for Main Sections Container
            const secContainer = document.getElementById('menus-sections-container');
            if (secContainer && typeof Sortable !== 'undefined') {
                window.menuSectionsSortable = new Sortable(secContainer, {
                    animation: 250,
                    handle: '.menu-section-drag-handle',
                    draggable: '[data-menu-section]',
                    ghostClass: 'dashboard-sortable-ghost',
                    dragClass: 'dashboard-sortable-drag',
                    chosenClass: 'dashboard-sortable-chosen',
                    disabled: true,
                    onEnd: function() {
                        const newOrder = [];
                        secContainer.querySelectorAll('[data-menu-section]').forEach(el => {
                            newOrder.push(el.dataset.menuSection);
                        });
                        window.menuSectionsOrder = newOrder;
                    }
                });
            }

            // 2. Sortable for Category Toolbar
            const catContainer = document.getElementById('menus-category-toolbar');
            if (catContainer && typeof Sortable !== 'undefined') {
                window.menusCatSortable = new Sortable(catContainer, {
                    animation: 200,
                    handle: '.menus-cat-drag-handle',
                    draggable: '.menu-category-pill',
                    ghostClass: 'dashboard-sortable-ghost',
                    dragClass: 'dashboard-sortable-drag',
                    chosenClass: 'dashboard-sortable-chosen',
                    disabled: true,
                    onEnd: function() {
                        const newOrder = [];
                        catContainer.querySelectorAll('[data-cat-slug]').forEach(el => newOrder.push(el.dataset.catSlug));
                        window.menusCategoryOrder = newOrder;
                    }
                });
            }

            // 3. Sortable for Menu Cards Grid
            const gridContainer = document.getElementById('menus-grid');
            if (gridContainer && typeof Sortable !== 'undefined') {
                window.menuCardsSortable = new Sortable(gridContainer, {
                    animation: 200,
                    handle: '.menu-card-drag-handle',
                    draggable: '[data-menu-wrapper]',
                    ghostClass: 'dashboard-sortable-ghost',
                    dragClass: 'dashboard-sortable-drag',
                    chosenClass: 'dashboard-sortable-chosen',
                    disabled: true,
                    onEnd: function() {
                        const newOrder = [];
                        gridContainer.querySelectorAll('[data-menu-wrapper]').forEach(el => {
                            const mId = parseInt(el.dataset.menuWrapper);
                            if (mId) newOrder.push(mId);
                        });
                        window.menuItemsOrder = newOrder;
                    }
                });
            }
        }

        function initMenusComponentStyles() {
            Object.keys(window.menusCurrentStyles).forEach(cId => {
                if (cId === 'category_pills') {
                    applyMenuPillShape(window.menusCurrentStyles[cId].shape);
                } else {
                    applyMenusComponentStyle(cId, window.menusCurrentStyles[cId]);
                }
            });
        }

        function toggleMenusEditMode() {
            window.menusIsEditMode = !window.menusIsEditMode;
            const bar  = document.getElementById('menus-edit-bar');
            const btn  = document.getElementById('menus-btn-text');
            const root = document.getElementById('menus-root');

            if (window.menusIsEditMode) {
                bar.classList.remove('hidden');
                btn.textContent = '✕ Keluar Mode Penyesuaian';
                root.classList.add('in-edit-mode');
                document.querySelectorAll('.menus-edit-controls').forEach(el => el.classList.remove('hidden'));
                document.querySelectorAll('.menu-section-drag-handle').forEach(el => el.classList.remove('hidden'));
                document.querySelectorAll('.menu-card-drag-handle').forEach(el => el.classList.remove('hidden'));
                document.querySelectorAll('.menus-cat-drag-handle').forEach(el => el.classList.remove('hidden'));
                document.querySelectorAll('[data-menu-wrapper], [data-widget-wrapper]').forEach(el => {
                    el.classList.add('p-1.5', 'rounded-2xl', 'border-2', 'border-dashed', 'border-amber-500/50', 'bg-amber-50/10');
                });
            } else {
                bar.classList.add('hidden');
                btn.textContent = '⚙️ Sesuaikan Tampilan';
                root.classList.remove('in-edit-mode');
                document.querySelectorAll('.menus-edit-controls').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('.menu-section-drag-handle').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('.menu-card-drag-handle').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('.menus-cat-drag-handle').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('[data-menu-wrapper], [data-widget-wrapper]').forEach(el => {
                    el.classList.remove('p-1.5', 'rounded-2xl', 'border-2', 'border-dashed', 'border-amber-500/50', 'bg-amber-50/10');
                });
            }

            if (window.menuSectionsSortable) window.menuSectionsSortable.option('disabled', !window.menusIsEditMode);
            if (window.menusCatSortable) window.menusCatSortable.option('disabled', !window.menusIsEditMode);
            if (window.menuCardsSortable) window.menuCardsSortable.option('disabled', !window.menusIsEditMode);
            if (window.menusIsEditMode) updateMenusStyleDisplays();
        }

        function setMenusComponentShape(cId, shape) {
            if (!window.menusCurrentStyles[cId]) window.menusCurrentStyles[cId] = {};
            window.menusCurrentStyles[cId].shape = shape;
            applyMenusComponentStyle(cId, window.menusCurrentStyles[cId]);

            document.querySelectorAll('.m-shape-btn-' + cId).forEach(btn => {
                const isActive = btn.dataset.shape === shape;
                btn.classList.toggle('bg-amber-500',   isActive);
                btn.classList.toggle('text-white',       isActive);
                btn.classList.toggle('border-amber-600', isActive);
                btn.classList.toggle('bg-white',        !isActive);
                btn.classList.toggle('text-slate-800',  !isActive);
                btn.classList.toggle('border-slate-300',!isActive);
            });
        }

        function onMenusSliderInput(cId, dim, value) {
            const v = parseInt(value);
            if (isNaN(v)) return;
            if (!window.menusCurrentStyles[cId]) window.menusCurrentStyles[cId] = {};
            window.menusCurrentStyles[cId][dim] = v;
            applyMenusComponentStyle(cId, window.menusCurrentStyles[cId]);
        }

        function applyMenusComponentStyle(cId, style) {
            if (!style) return;
            const visualCard = document.querySelector('[data-component-id="' + cId + '"]');
            if (!visualCard) return;

            const isMenuItem = cId.startsWith('menu_item_');
            const defaultW   = isMenuItem ? 300 : 900;
            const defaultH   = isMenuItem ? 260 : (cId === 'M-HEADER' ? 120 : (cId === 'M-GRID' ? 450 : 80));
            const shape      = style.shape || 'rectangle';

            const reqWidth  = parseInt(style.width)  || defaultW;
            const reqHeight = parseInt(style.height) || defaultH;

            const bounds = isMenuItem
                ? { minW: 180, maxW: 500, minH: 140, maxH: 450 }
                : { minW: 240, maxW: 900, minH: 60,  maxH: 600 };

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

            const display = document.getElementById('m-size-display-' + cId);
            if (display) display.textContent = `${actualW} × ${actualH} px (Req: ${reqWidth}×${reqHeight})`;

            const wSlider = document.getElementById('m-width-slider-'  + cId);
            const hSlider = document.getElementById('m-height-slider-' + cId);
            const wVal    = document.getElementById('m-wval-'          + cId);
            const hVal    = document.getElementById('m-hval-'          + cId);

            if (wSlider) wSlider.value = reqWidth;
            if (hSlider) hSlider.value = reqHeight;
            if (wVal)    wVal.textContent = `${reqWidth}px`;
            if (hVal)    hVal.textContent = `${reqHeight}px`;
        }

        function updateMenusStyleDisplays() {
            Object.keys(window.menusCurrentStyles).forEach(cId => {
                const style = window.menusCurrentStyles[cId];
                if (!style) return;
                const isMenuItem = cId.startsWith('menu_item_');
                const defaultW   = isMenuItem ? 300 : 900;
                const defaultH   = isMenuItem ? 260 : (cId === 'M-HEADER' ? 120 : (cId === 'M-GRID' ? 450 : 80));

                const w = style.width  || defaultW;
                const h = style.height || defaultH;

                const wSlider = document.getElementById('m-width-slider-'  + cId);
                const hSlider = document.getElementById('m-height-slider-' + cId);
                const wVal    = document.getElementById('m-wval-'          + cId);
                const hVal    = document.getElementById('m-hval-'          + cId);

                if (wSlider) wSlider.value = w;
                if (hSlider) hSlider.value = h;
                if (wVal)    wVal.textContent = `${w}px`;
                if (hVal)    hVal.textContent = `${h}px`;

                if (style.shape) setMenusComponentShape(cId, style.shape);
            });
        }

        function setMenuPillShape(shape) {
            if (!window.menusCurrentStyles.category_pills) window.menusCurrentStyles.category_pills = {};
            window.menusCurrentStyles.category_pills.shape = shape;
            applyMenuPillShape(shape);
        }

        function applyMenuPillShape(shape) {
            const pillLinks = document.querySelectorAll('#menus-category-toolbar a, #menus-root a[href*="menus.index"]');
            pillLinks.forEach(a => {
                switch (shape) {
                    case 'rounded':   a.style.borderRadius = '16px'; break;
                    case 'sharp':     a.style.borderRadius = '0px';  break;
                    case 'rectangle': a.style.borderRadius = '6px';  break;
                    default:          a.style.borderRadius = '9999px'; // pill
                }
            });

            document.querySelectorAll('.m-shape-btn').forEach(btn => {
                const isActive = btn.dataset.shape === shape;
                btn.classList.toggle('bg-amber-500',    isActive);
                btn.classList.toggle('border-amber-600', isActive);
                btn.classList.toggle('text-white',       isActive);
                btn.classList.toggle('bg-white',        !isActive);
                btn.classList.toggle('text-slate-800',  !isActive);
                btn.classList.toggle('border-slate-300',!isActive);
            });
        }

        function saveMenusLayout() {
            fetch('{{ route("workspace.save-layout") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ layout_matrix: {
                    menu_sections_order:   window.menuSectionsOrder,
                    menu_categories_order: window.menusCategoryOrder,
                    menu_items_order:      window.menuItemsOrder,
                    component_styles: { menus: JSON.parse(JSON.stringify(window.menusCurrentStyles)) }
                }})
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    window.menusLastSavedStyles   = JSON.parse(JSON.stringify(window.menusCurrentStyles));
                    window.menuSectionsLastSaved  = [...window.menuSectionsOrder];
                    window.menusCategoryLastSaved = [...window.menusCategoryOrder];
                    window.menuItemsLastSaved     = [...window.menuItemsOrder];
                    showMenusToast('✓ Urutan & tampilan katalog menu berhasil disimpan.', 'success');
                    toggleMenusEditMode();
                } else {
                    showMenusToast('Gagal menyimpan: ' + (data.message || 'Error'), 'error');
                }
            })
            .catch(() => showMenusToast('Kesalahan koneksi saat menyimpan layout.', 'error'));
        }

        function cancelMenusLayout() {
            window.menusCurrentStyles = JSON.parse(JSON.stringify(window.menusLastSavedStyles));
            window.menuSectionsOrder  = [...window.menuSectionsLastSaved];
            window.menusCategoryOrder = [...window.menusCategoryLastSaved];
            window.menuItemsOrder     = [...window.menuItemsLastSaved];

            // Reorder sections in DOM
            const secContainer = document.getElementById('menus-sections-container');
            if (secContainer && window.menuSectionsOrder.length) {
                const secs = {};
                secContainer.querySelectorAll('[data-menu-section]').forEach(s => { secs[s.dataset.menuSection] = s; });
                window.menuSectionsOrder.forEach(secId => { if (secs[secId]) secContainer.appendChild(secs[secId]); });
            }

            // Reorder categories in DOM
            const catContainer = document.getElementById('menus-category-toolbar');
            if (catContainer && window.menusCategoryOrder.length) {
                const pills = {};
                catContainer.querySelectorAll('[data-cat-slug]').forEach(p => { pills[p.dataset.catSlug] = p; });
                window.menusCategoryOrder.forEach(slug => { if (pills[slug]) catContainer.appendChild(pills[slug]); });
            }

            // Reorder menu cards in DOM
            const gridContainer = document.getElementById('menus-grid');
            if (gridContainer && window.menuItemsOrder.length) {
                const wrappers = {};
                gridContainer.querySelectorAll('[data-menu-wrapper]').forEach(w => { wrappers[parseInt(w.dataset.menuWrapper)] = w; });
                window.menuItemsOrder.forEach(mId => { if (wrappers[mId]) gridContainer.appendChild(wrappers[mId]); });
            }

            Object.keys(window.menusCurrentStyles).forEach(cId => {
                if (cId === 'category_pills') applyMenuPillShape(window.menusCurrentStyles[cId].shape);
                else applyMenusComponentStyle(cId, window.menusCurrentStyles[cId]);
            });
            showMenusToast('Perubahan dibatalkan.', 'info');
            toggleMenusEditMode();
        }

        function resetMenusLayout() {
            if (!confirm('Reset tampilan katalog menu ke default sistem?')) return;
            fetch('{{ route("workspace.reset-layout") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ page: 'menus' })
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    const defaultSecOrder = data.layout_matrix?.menu_sections_order || ['M-HEADER', 'M-TOOLBAR', 'M-GRID'];
                    const defaultCatOrder = data.layout_matrix?.menu_categories_order || [];
                    const defaultItemOrder = data.layout_matrix?.menu_items_order || [];
                    window.menuSectionsOrder    = [...defaultSecOrder];
                    window.menuSectionsLastSaved= [...defaultSecOrder];
                    window.menusCategoryOrder   = [...defaultCatOrder];
                    window.menusCategoryLastSaved = [...defaultCatOrder];
                    window.menuItemsOrder       = [...defaultItemOrder];
                    window.menuItemsLastSaved   = [...defaultItemOrder];

                    const resetStyles = (data.layout_matrix?.component_styles || {}).menus || window.menusDefaultStyles;
                    window.menusCurrentStyles   = Object.assign({}, resetStyles);
                    window.menusLastSavedStyles = JSON.parse(JSON.stringify(window.menusCurrentStyles));

                    // Reorder sections to default in DOM
                    const secContainer = document.getElementById('menus-sections-container');
                    if (secContainer && window.menuSectionsOrder.length) {
                        const secs = {};
                        secContainer.querySelectorAll('[data-menu-section]').forEach(s => { secs[s.dataset.menuSection] = s; });
                        window.menuSectionsOrder.forEach(secId => { if (secs[secId]) secContainer.appendChild(secs[secId]); });
                    }

                    Object.keys(window.menusCurrentStyles).forEach(cId => {
                        if (cId === 'category_pills') applyMenuPillShape(window.menusCurrentStyles[cId].shape);
                        else applyMenusComponentStyle(cId, window.menusCurrentStyles[cId]);
                    });
                    showMenusToast('✓ Tampilan katalog dikembalikan ke default.', 'success');
                    if (window.menusIsEditMode) toggleMenusEditMode();
                }
            })
            .catch(() => showMenusToast('Gagal melakukan reset layout.', 'error'));
        }

        function resetSingleMenusComponent(cId) {
            const isMenuItem = cId.startsWith('menu_item_');
            const defaults = (window.menusDefaultStyles && window.menusDefaultStyles[cId]) || (
                isMenuItem ? { shape: 'rectangle', width: 300, height: 260, border_radius: 16 } : { shape: 'rectangle', width: 900, height: 120, border_radius: 16 }
            );
            window.menusCurrentStyles[cId] = Object.assign({}, defaults);
            applyMenusComponentStyle(cId, window.menusCurrentStyles[cId]);
            updateMenusStyleDisplays();
            showMenusToast('✓ Komponen dikembalikan ke default.', 'success');
        }

        function showMenusToast(msg, type = 'success') {
            const toast = document.getElementById('menus-toast');
            const msgEl = document.getElementById('menus-toast-message');
            const icon  = document.getElementById('menus-toast-icon');
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
