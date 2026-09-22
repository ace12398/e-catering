<x-dashboard-layout>
    <x-slot name="title">Keranjang Belanja — E-Catering</x-slot>
    <x-slot name="toolbarTitle">Keranjang & Pembayaran</x-slot>

    {{-- SortableJS untuk Section Drag & Drop Reorder --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

    @php
        $effectiveMatrix = $layout_matrix ?? auth()->user()?->preference?->effective_layout_matrix ?? \App\Models\WorkspacePreference::getDefaultLayoutMatrix();
        $cartSavedStyles = $effectiveMatrix['component_styles']['cart'] ?? [];
        $defaultCartStyles = [
            'C-CART-HEADER'  => ['shape' => 'rectangle', 'width' => 900, 'height' => 80,  'border_radius' => 16],
            'C-CART-ITEMS'   => ['shape' => 'rectangle', 'width' => 600, 'height' => 300, 'border_radius' => 16],
            'C-CART-GUIDE'   => ['shape' => 'rectangle', 'width' => 600, 'height' => 140, 'border_radius' => 16],
            'C-CART-SUMMARY' => ['shape' => 'rectangle', 'width' => 320, 'height' => 450, 'border_radius' => 16],
        ];
        $cartSectionsOrder = $cartSectionsOrder ?? $effectiveMatrix['cart_sections_order'] ?? ['C-CART-HEADER', 'C-CART-ITEMS', 'C-CART-GUIDE', 'C-CART-SUMMARY'];
    @endphp

    {{-- STYLE OVERRIDES FOR SORTABLE DROPZONE AND EDIT STATE --}}
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
        .cart-section-drag-handle {
            user-select: none;
            touch-action: none;
        }
    </style>

    <div class="max-w-6xl mx-auto space-y-6 relative" id="caterflow-cart-root">
        {{-- Flash Messages --}}
        @if(session('success') || session('status'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-bold flex items-center space-x-2">
                <span>✅</span>
                <span>{{ session('success') ?? session('status') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs font-bold">
                ❌ {{ session('error') }}
            </div>
        @endif

        {{-- TOAST NOTIFICATION --}}
        <div id="cart-toast" class="hidden fixed bottom-6 right-6 z-50 p-4 rounded-xl bg-slate-900 text-white shadow-2xl border border-slate-700 flex items-center space-x-3 transition-all transform translate-y-4 opacity-0">
            <span id="cart-toast-icon" class="text-amber-400 text-lg font-bold">✓</span>
            <span id="cart-toast-message" class="text-xs font-medium">Layout keranjang berhasil diperbarui.</span>
        </div>

        {{-- CANONICAL ADAPTIVE MODE BANNER (MATCHING /finance & /dashboard) --}}
        <div id="cart-edit-mode-bar" class="hidden sticky top-4 z-40 p-4 rounded-2xl bg-amber-950/90 dark:bg-amber-950/95 backdrop-blur border-2 border-amber-500 text-white shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center space-x-3">
                <span class="p-2.5 rounded-xl bg-amber-500/20 border border-amber-400/30 text-amber-300 font-mono text-base">✨</span>
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-300">Mode Penyesuaian Tampilan Aktif — Keranjang Belanja</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500 text-slate-950">Drag & Drop Aktif</span>
                    </div>
                    <p class="text-xs text-amber-100/90 mt-0.5">Ubah bentuk (shape), ukuran (lebar/tinggi), dan geser urutan bagian keranjang secara live.</p>
                </div>
            </div>
            <div class="flex items-center space-x-2 shrink-0">
                <button type="button" onclick="cancelCartLayout()" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs transition-colors border border-slate-600">
                    ✕ Batal
                </button>
                <button type="button" onclick="resetCartLayout()" class="px-3.5 py-2 rounded-xl bg-amber-900/80 hover:bg-amber-800 text-amber-200 font-semibold text-xs transition-colors border border-amber-600">
                    🔄 Reset Default
                </button>
                <button type="button" onclick="saveCartLayout()" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold text-xs shadow-md transition-colors flex items-center space-x-1.5">
                    <span>💾 Simpan Tampilan</span>
                </button>
            </div>
        </div>

        {{-- MAIN SECTIONS CONTAINER (WORKSPACE SECTIONS REORDERABLE) --}}
        <div id="cart-sections-container" class="space-y-6">
            @foreach($cartSectionsOrder as $sectionId)
                @if($sectionId === 'C-CART-HEADER')
                    {{-- 1. HEADER COMPONENT (Component ID: C-CART-HEADER) --}}
                    <div data-cart-section="C-CART-HEADER" data-widget-wrapper="C-CART-HEADER" class="cart-component-item w-full flex flex-col space-y-3 transition-all relative">
                        {{-- Dedicated Section Drag Handle --}}
                        <div class="cart-section-drag-handle hidden self-start px-2.5 py-1 rounded-lg bg-amber-500/20 hover:bg-amber-500 text-amber-800 dark:text-amber-200 hover:text-white border border-amber-400/40 cursor-grab active:cursor-grabbing font-mono text-xs font-bold transition-colors select-none shadow-sm mb-1">
                            ⠿ GESER BAGIAN: Header Keranjang
                        </div>

                        {{-- VISUAL CARD --}}
                        <div id="cart-widget-card-C-CART-HEADER" data-component-id="C-CART-HEADER" class="caterflow-visual-card w-full p-4 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4 overflow-hidden transition-all">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900 dark:text-white stat-value">Keranjang Belanja Katering</h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400 subtext">Tinjau item katering pilihan Anda, sesuaikan jumlah porsi, dan lakukan pemesanan.</p>
                            </div>
                            <div class="flex items-center space-x-2 shrink-0">
                                <a href="{{ route('menus.index') }}" class="px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-200 text-xs font-bold transition-colors">
                                    + Tambah Menu
                                </a>
                                <button type="button" id="cart-btn-edit" onclick="toggleCartEditMode()"
                                        class="h-9 px-4 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs transition-colors flex items-center space-x-1.5 shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <span id="cart-btn-text">⚙️ Sesuaikan Tampilan</span>
                                </button>
                            </div>
                        </div>

                        {{-- SEPARATE RECTANGULAR CONFIGURATION EDITOR (OUTSIDE PREVIEW) --}}
                        <div class="cart-edit-controls hidden w-full p-4 rounded-xl bg-slate-900/90 text-white border border-slate-700/60 shadow-lg space-y-3 text-xs backdrop-blur z-20">
                            <div class="flex items-center justify-between font-bold border-b border-slate-700 pb-2 text-xs">
                                <span class="text-amber-400 font-bold">Atur Komponen: Header Keranjang (C-CART-HEADER)</span>
                                <button type="button" onclick="resetSingleCartComponent('C-CART-HEADER')" class="px-2 py-1 rounded text-xs font-semibold bg-amber-800/80 hover:bg-amber-700 text-white border border-amber-600 transition-colors" title="Reset komponen ini">🔄 Reset</button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1.5">Bentuk</span>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach(['rectangle'=>'■ Kotak','rounded'=>'◉ Bulat','sharp'=>'▪ Tajam','pill'=>'⬭ Pil','circle'=>'○ Lingkar','hexagon'=>'⬡ Hex'] as $sId => $sLabel)
                                            <button type="button" onclick="setCartComponentShape('C-CART-HEADER', '{{ $sId }}')" class="c-shape-btn-C-CART-HEADER px-2.5 py-1.5 rounded-lg text-xs font-semibold border bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-600 text-slate-800 dark:text-slate-200 hover:bg-amber-500 hover:text-white transition-colors" data-shape="{{ $sId }}">{{ $sLabel }}</button>
                                        @endforeach
                                    </div>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                        <span>Lebar</span>
                                        <span id="c-wval-C-CART-HEADER" class="text-amber-400 font-mono font-bold">900px</span>
                                    </div>
                                    <input type="range" min="240" max="900" step="10" value="900" id="c-width-slider-C-CART-HEADER" oninput="onCartSliderInput('C-CART-HEADER', 'width', this.value)" class="w-full accent-amber-500">
                                </div>
                                <div>
                                    <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                        <span>Tinggi</span>
                                        <span id="c-hval-C-CART-HEADER" class="text-amber-400 font-mono font-bold">80px</span>
                                    </div>
                                    <input type="range" min="60" max="300" step="10" value="80" id="c-height-slider-C-CART-HEADER" oninput="onCartSliderInput('C-CART-HEADER', 'height', this.value)" class="w-full accent-amber-500">
                                </div>
                            </div>
                            <div class="text-[11px] font-mono text-amber-400 pt-1.5 border-t border-slate-700/60" id="c-size-display-C-CART-HEADER">900 × 80 px</div>
                        </div>
                    </div>

                @elseif($sectionId === 'C-CART-ITEMS')
                    {{-- 2. CART ITEMS LIST (Component ID: C-CART-ITEMS) --}}
                    <div data-cart-section="C-CART-ITEMS" data-widget-wrapper="C-CART-ITEMS" class="cart-component-item w-full flex flex-col space-y-3 transition-all relative">
                        {{-- Dedicated Section Drag Handle --}}
                        <div class="cart-section-drag-handle hidden self-start px-2.5 py-1 rounded-lg bg-amber-500/20 hover:bg-amber-500 text-amber-800 dark:text-amber-200 hover:text-white border border-amber-400/40 cursor-grab active:cursor-grabbing font-mono text-xs font-bold transition-colors select-none shadow-sm mb-1">
                            ⠿ GESER BAGIAN: Daftar Menu Dipilih
                        </div>

                        {{-- VISUAL CARD --}}
                        <div id="cart-widget-card-C-CART-ITEMS" data-component-id="C-CART-ITEMS" class="caterflow-visual-card w-full p-6 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4 overflow-hidden transition-all">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider border-b border-slate-100 dark:border-slate-800 pb-3 flex justify-between items-center flex-between-header">
                                <span class="stat-value">Daftar Menu Dipilih ({{ $cartItems->count() }})</span>
                                @if($cartItems->count() > 0)
                                    <span class="text-xs text-slate-400 font-normal subtext">Subtotal: {{ format_idr($subtotal) }}</span>
                                @endif
                            </h3>

                            @forelse($cartItems as $item)
                                @php
                                    $unitPrice = $item->unit_price ?? $item->menu?->price ?? 0;
                                    $itemSubtotal = $unitPrice * $item->quantity;
                                @endphp
                                <div class="cart-item-box p-4 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800 flex items-center justify-center font-bold text-2xl shrink-0 adaptive-icon">
                                            🍱
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-bold text-slate-900 dark:text-white stat-value">{{ $item->menu?->name ?? 'Menu Katering' }}</h4>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 subtext">{{ format_idr($unitPrice) }} / porsi</p>
                                            @if($item->notes)
                                                <p class="text-[11px] text-amber-600 dark:text-amber-400 mt-0.5 subtext">📝 {{ $item->notes }}</p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between sm:justify-end space-x-4">
                                        {{-- QUANTITY CONTROLS (NATIVE FORMS — SAFE FROM DRAG EVENTS) --}}
                                        <div class="flex items-center space-x-1">
                                            <form method="POST" action="{{ route('cart.update', $item->id) }}" class="inline-flex">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="quantity" value="{{ max(1, $item->quantity - 1) }}">
                                                <button type="submit" class="w-7 h-7 rounded-lg bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-800 dark:text-white font-bold text-xs flex items-center justify-center transition-colors">-</button>
                                            </form>
                                            <span class="w-8 text-center text-xs font-extrabold text-slate-900 dark:text-white stat-value">{{ $item->quantity }}</span>
                                            <form method="POST" action="{{ route('cart.update', $item->id) }}" class="inline-flex">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="quantity" value="{{ $item->quantity + 1 }}">
                                                <button type="submit" class="w-7 h-7 rounded-lg bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-800 dark:text-white font-bold text-xs flex items-center justify-center transition-colors">+</button>
                                            </form>
                                        </div>

                                        <div class="text-sm font-extrabold text-slate-900 dark:text-white min-w-[100px] text-right stat-value">
                                            {{ format_idr($itemSubtotal) }}
                                        </div>

                                        <form method="POST" action="{{ route('cart.remove', $item->id) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" title="Hapus dari keranjang" class="text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/30 p-1.5 rounded-lg transition-colors text-xs font-bold">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <div class="p-12 text-center space-y-3">
                                    <div class="text-4xl">🛒</div>
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">Keranjang Masih Kosong</h4>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Jelajahi katalog katering kami untuk menambahkan paket hidangan.</p>
                                    <a href="{{ route('menus.index') }}" class="inline-block px-5 py-2.5 rounded-xl bg-amber-600 text-white font-bold text-xs hover:bg-amber-500 transition-colors shadow-sm">
                                        Jelajahi Katalog Sekarang ➔
                                    </a>
                                </div>
                            @endforelse
                        </div>

                        {{-- SEPARATE RECTANGULAR CONFIGURATION EDITOR (OUTSIDE PREVIEW) --}}
                        <div class="cart-edit-controls hidden w-full p-4 rounded-xl bg-slate-900/90 text-white border border-slate-700/60 shadow-lg space-y-3 text-xs backdrop-blur z-20">
                            <div class="flex items-center justify-between font-bold border-b border-slate-700 pb-2 text-xs">
                                <span class="text-amber-400 font-bold">Atur Komponen: Daftar Menu Dipilih (C-CART-ITEMS)</span>
                                <button type="button" onclick="resetSingleCartComponent('C-CART-ITEMS')" class="px-2 py-1 rounded text-xs font-semibold bg-amber-800/80 hover:bg-amber-700 text-white border border-amber-600 transition-colors" title="Reset komponen ini">🔄 Reset</button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1.5">Bentuk</span>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach(['rectangle'=>'■ Kotak','rounded'=>'◉ Bulat','sharp'=>'▪ Tajam','pill'=>'⬭ Pil','circle'=>'○ Lingkar','hexagon'=>'⬡ Hex'] as $sId => $sLabel)
                                            <button type="button" onclick="setCartComponentShape('C-CART-ITEMS', '{{ $sId }}')" class="c-shape-btn-C-CART-ITEMS px-2.5 py-1.5 rounded-lg text-xs font-semibold border bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-600 text-slate-800 dark:text-slate-200 hover:bg-amber-500 hover:text-white transition-colors" data-shape="{{ $sId }}">{{ $sLabel }}</button>
                                        @endforeach
                                    </div>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                        <span>Lebar</span>
                                        <span id="c-wval-C-CART-ITEMS" class="text-amber-400 font-mono font-bold">600px</span>
                                    </div>
                                    <input type="range" min="240" max="900" step="10" value="600" id="c-width-slider-C-CART-ITEMS" oninput="onCartSliderInput('C-CART-ITEMS', 'width', this.value)" class="w-full accent-amber-500">
                                </div>
                                <div>
                                    <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                        <span>Tinggi</span>
                                        <span id="c-hval-C-CART-ITEMS" class="text-amber-400 font-mono font-bold">300px</span>
                                    </div>
                                    <input type="range" min="150" max="600" step="10" value="300" id="c-height-slider-C-CART-ITEMS" oninput="onCartSliderInput('C-CART-ITEMS', 'height', this.value)" class="w-full accent-amber-500">
                                </div>
                            </div>
                            <div class="text-[11px] font-mono text-amber-400 pt-1.5 border-t border-slate-700/60" id="c-size-display-C-CART-ITEMS">600 × 300 px</div>
                        </div>
                    </div>

                @elseif($sectionId === 'C-CART-GUIDE')
                    {{-- 3. PAYMENT GUIDE CARD (Component ID: C-CART-GUIDE) --}}
                    @if($cartItems->count() > 0)
                    <div data-cart-section="C-CART-GUIDE" data-widget-wrapper="C-CART-GUIDE" class="cart-component-item w-full flex flex-col space-y-3 transition-all relative">
                        {{-- Dedicated Section Drag Handle --}}
                        <div class="cart-section-drag-handle hidden self-start px-2.5 py-1 rounded-lg bg-amber-500/20 hover:bg-amber-500 text-amber-800 dark:text-amber-200 hover:text-white border border-amber-400/40 cursor-grab active:cursor-grabbing font-mono text-xs font-bold transition-colors select-none shadow-sm mb-1">
                            ⠿ GESER BAGIAN: Panduan Pembayaran
                        </div>

                        {{-- VISUAL CARD --}}
                        <div id="cart-widget-card-C-CART-GUIDE" data-component-id="C-CART-GUIDE" class="caterflow-visual-card w-full p-5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-3 overflow-hidden transition-all">
                            <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase stat-value">💳 Panduan Pembayaran</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="p-3 rounded-xl bg-white dark:bg-slate-800 border border-indigo-200 dark:border-indigo-900/60 space-y-1">
                                    <p class="text-xs font-bold text-indigo-700 dark:text-indigo-400">📱 QRIS</p>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 subtext">Scan QR Code di halaman detail pesanan setelah checkout.</p>
                                </div>
                                <div class="p-3 rounded-xl bg-white dark:bg-slate-800 border border-emerald-200 dark:border-emerald-900/60 space-y-1">
                                    <p class="text-xs font-bold text-emerald-700 dark:text-emerald-400">🏦 Transfer Bank</p>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 subtext">Nomor rekening akan tampil di halaman pesanan.</p>
                                </div>
                                <div class="p-3 rounded-xl bg-white dark:bg-slate-800 border border-amber-200 dark:border-amber-900/60 space-y-1">
                                    <p class="text-xs font-bold text-amber-700 dark:text-amber-400">💵 Tunai</p>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 subtext">Bayar tunai saat pesanan tiba di lokasi Anda.</p>
                                </div>
                            </div>
                        </div>

                        {{-- SEPARATE RECTANGULAR CONFIGURATION EDITOR (OUTSIDE PREVIEW) --}}
                        <div class="cart-edit-controls hidden w-full p-4 rounded-xl bg-slate-900/90 text-white border border-slate-700/60 shadow-lg space-y-3 text-xs backdrop-blur z-20">
                            <div class="flex items-center justify-between font-bold border-b border-slate-700 pb-2 text-xs">
                                <span class="text-amber-400 font-bold">Atur Komponen: Panduan Pembayaran (C-CART-GUIDE)</span>
                                <button type="button" onclick="resetSingleCartComponent('C-CART-GUIDE')" class="px-2 py-1 rounded text-xs font-semibold bg-amber-800/80 hover:bg-amber-700 text-white border border-amber-600 transition-colors" title="Reset komponen ini">🔄 Reset</button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1.5">Bentuk</span>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach(['rectangle'=>'■ Kotak','rounded'=>'◉ Bulat','sharp'=>'▪ Tajam','pill'=>'⬭ Pil','circle'=>'○ Lingkar','hexagon'=>'⬡ Hex'] as $sId => $sLabel)
                                            <button type="button" onclick="setCartComponentShape('C-CART-GUIDE', '{{ $sId }}')" class="c-shape-btn-C-CART-GUIDE px-2.5 py-1.5 rounded-lg text-xs font-semibold border bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-600 text-slate-800 dark:text-slate-200 hover:bg-amber-500 hover:text-white transition-colors" data-shape="{{ $sId }}">{{ $sLabel }}</button>
                                        @endforeach
                                    </div>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                        <span>Lebar</span>
                                        <span id="c-wval-C-CART-GUIDE" class="text-amber-400 font-mono font-bold">600px</span>
                                    </div>
                                    <input type="range" min="240" max="900" step="10" value="600" id="c-width-slider-C-CART-GUIDE" oninput="onCartSliderInput('C-CART-GUIDE', 'width', this.value)" class="w-full accent-amber-500">
                                </div>
                                <div>
                                    <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                        <span>Tinggi</span>
                                        <span id="c-hval-C-CART-GUIDE" class="text-amber-400 font-mono font-bold">140px</span>
                                    </div>
                                    <input type="range" min="80" max="300" step="10" value="140" id="c-height-slider-C-CART-GUIDE" oninput="onCartSliderInput('C-CART-GUIDE', 'height', this.value)" class="w-full accent-amber-500">
                                </div>
                            </div>
                            <div class="text-[11px] font-mono text-amber-400 pt-1.5 border-t border-slate-700/60" id="c-size-display-C-CART-GUIDE">600 × 140 px</div>
                        </div>
                    </div>
                    @endif

                @elseif($sectionId === 'C-CART-SUMMARY')
                    {{-- 4. ORDER SUMMARY & CHECKOUT FORM (Component ID: C-CART-SUMMARY) --}}
                    <div data-cart-section="C-CART-SUMMARY" data-widget-wrapper="C-CART-SUMMARY" class="cart-component-item w-full flex flex-col space-y-3 transition-all relative">
                        {{-- Dedicated Section Drag Handle --}}
                        <div class="cart-section-drag-handle hidden self-start px-2.5 py-1 rounded-lg bg-amber-500/20 hover:bg-amber-500 text-amber-800 dark:text-amber-200 hover:text-white border border-amber-400/40 cursor-grab active:cursor-grabbing font-mono text-xs font-bold transition-colors select-none shadow-sm mb-1">
                            ⠿ GESER BAGIAN: Ringkasan & Formulir Checkout
                        </div>

                        {{-- VISUAL CARD --}}
                        <div id="cart-widget-card-C-CART-SUMMARY" data-component-id="C-CART-SUMMARY" class="caterflow-visual-card w-full p-6 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4 overflow-hidden transition-all">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider border-b border-slate-100 dark:border-slate-800 pb-3 stat-value">
                                Ringkasan Pembayaran
                            </h3>

                            <div class="space-y-2.5 text-xs">
                                <div class="flex justify-between text-slate-600 dark:text-slate-400">
                                    <span>Subtotal Menu</span>
                                    <span class="font-bold text-slate-900 dark:text-white">{{ format_idr($subtotal) }}</span>
                                </div>
                                <div class="flex justify-between text-slate-600 dark:text-slate-400">
                                    <span>Pajak (PPN 11%)</span>
                                    <span class="font-bold text-slate-900 dark:text-white">{{ format_idr($tax) }}</span>
                                </div>
                                <div class="flex justify-between text-slate-600 dark:text-slate-400">
                                    <span>Biaya Pengiriman</span>
                                    <span class="font-bold text-slate-900 dark:text-white">{{ format_idr($deliveryFee) }}</span>
                                </div>
                                <div class="border-t border-slate-100 dark:border-slate-800 pt-3 flex justify-between text-sm font-extrabold text-slate-900 dark:text-white">
                                    <span>Total Biaya</span>
                                    <span class="text-amber-600 dark:text-amber-400 stat-value">{{ format_idr($total) }}</span>
                                </div>
                            </div>

                            @if($cartItems->count() > 0)
                                <form method="POST" action="{{ route('orders.store') }}" class="space-y-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                                    @csrf

                                    <div class="space-y-1">
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase subtext">Alamat Pengiriman *</label>
                                        <textarea name="delivery_address" rows="2" required
                                                  class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                                                  placeholder="Masukkan alamat pengiriman katering...">{{ auth()->user()->profile?->address ?? '' }}</textarea>
                                    </div>

                                    <div class="space-y-1">
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase subtext">Metode Pembayaran *</label>
                                        <select name="payment_method_id" required id="payment-method-select"
                                                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                                            @foreach($paymentMethods as $pm)
                                                <option value="{{ $pm->id }}" data-type="{{ strtolower($pm->code ?? $pm->type) }}">
                                                    {{ $pm->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <p id="payment-hint" class="text-[11px] text-slate-400 mt-1 subtext">
                                            Setelah checkout, panduan pembayaran akan tampil di halaman detail pesanan.
                                        </p>
                                    </div>

                                    <div class="space-y-1">
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase subtext">Catatan Khusus</label>
                                        <input type="text" name="notes" placeholder="Contoh: Sertakan sendok & tisu tambahan"
                                               class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:border-amber-500">
                                    </div>

                                    <button type="submit" class="w-full py-3 rounded-xl bg-amber-600 text-white font-bold text-xs hover:bg-amber-500 transition-colors shadow-sm">
                                        Buat Pesanan 🚀
                                    </button>
                                    <p class="text-[11px] text-slate-400 text-center subtext">Setelah buat pesanan, Anda akan diarahkan ke halaman pembayaran.</p>
                                </form>
                            @endif
                        </div>

                        {{-- SEPARATE RECTANGULAR CONFIGURATION EDITOR (OUTSIDE PREVIEW) --}}
                        <div class="cart-edit-controls hidden w-full p-4 rounded-xl bg-slate-900/90 text-white border border-slate-700/60 shadow-lg space-y-3 text-xs backdrop-blur z-20">
                            <div class="flex items-center justify-between font-bold border-b border-slate-700 pb-2 text-xs">
                                <span class="text-amber-400 font-bold">Atur Komponen: Ringkasan & Checkout (C-CART-SUMMARY)</span>
                                <button type="button" onclick="resetSingleCartComponent('C-CART-SUMMARY')" class="px-2 py-1 rounded text-xs font-semibold bg-amber-800/80 hover:bg-amber-700 text-white border border-amber-600 transition-colors" title="Reset komponen ini">🔄 Reset</button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1.5">Bentuk</span>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach(['rectangle'=>'■ Kotak','rounded'=>'◉ Bulat','sharp'=>'▪ Tajam','pill'=>'⬭ Pil','circle'=>'○ Lingkar','hexagon'=>'⬡ Hex'] as $sId => $sLabel)
                                            <button type="button" onclick="setCartComponentShape('C-CART-SUMMARY', '{{ $sId }}')" class="c-shape-btn-C-CART-SUMMARY px-2.5 py-1.5 rounded-lg text-xs font-semibold border bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-600 text-slate-800 dark:text-slate-200 hover:bg-amber-500 hover:text-white transition-colors" data-shape="{{ $sId }}">{{ $sLabel }}</button>
                                        @endforeach
                                    </div>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                        <span>Lebar</span>
                                        <span id="c-wval-C-CART-SUMMARY" class="text-amber-400 font-mono font-bold">320px</span>
                                    </div>
                                    <input type="range" min="160" max="900" step="10" value="320" id="c-width-slider-C-CART-SUMMARY" oninput="onCartSliderInput('C-CART-SUMMARY', 'width', this.value)" class="w-full accent-amber-500">
                                </div>
                                <div>
                                    <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                        <span>Tinggi</span>
                                        <span id="c-hval-C-CART-SUMMARY" class="text-amber-400 font-mono font-bold">450px</span>
                                    </div>
                                    <input type="range" min="200" max="600" step="10" value="450" id="c-height-slider-C-CART-SUMMARY" oninput="onCartSliderInput('C-CART-SUMMARY', 'height', this.value)" class="w-full accent-amber-500">
                                </div>
                            </div>
                            <div class="text-[11px] font-mono text-amber-400 pt-1.5 border-t border-slate-700/60" id="c-size-display-C-CART-SUMMARY">320 × 450 px</div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- ADAPTIVE ENGINE SCRIPT FOR CART PAGE (CANONICAL MASTER /finance) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.cartIsEditMode       = false;
            window.cartSectionsOrder    = @json($cartSectionsOrder);
            window.cartSectionsLastSaved= [...window.cartSectionsOrder];
            window.cartSavedStyles      = @json($cartSavedStyles);
            window.cartDefaultStyles    = @json($defaultCartStyles);
            window.cartCurrentStyles    = Object.assign({}, window.cartDefaultStyles, window.cartSavedStyles);
            window.cartLastSavedStyles  = JSON.parse(JSON.stringify(window.cartCurrentStyles));
            window.cartSectionsSortable = null;

            initCartSortables();
            initCartComponentStyles();

            const select = document.getElementById('payment-method-select');
            const hint = document.getElementById('payment-hint');
            if (select && hint) {
                const updateHint = () => {
                    const opt = select.options[select.selectedIndex];
                    const name = (opt?.text || '').toLowerCase();
                    if (name.includes('qris')) {
                        hint.textContent = '📱 QR Code QRIS akan ditampilkan di halaman pesanan untuk dipindai.';
                        hint.className = 'text-[11px] text-indigo-600 dark:text-indigo-400 mt-1 font-semibold subtext';
                    } else if (name.includes('transfer')) {
                        hint.textContent = '🏦 Nomor rekening tujuan transfer akan tampil di halaman pesanan.';
                        hint.className = 'text-[11px] text-emerald-600 dark:text-emerald-400 mt-1 font-semibold subtext';
                    } else if (name.includes('tunai') || name.includes('cash')) {
                        hint.textContent = '💵 Pembayaran tunai dilakukan saat pesanan tiba. Tidak perlu upload bukti.';
                        hint.className = 'text-[11px] text-amber-600 dark:text-amber-400 mt-1 font-semibold subtext';
                    } else {
                        hint.textContent = 'Setelah checkout, panduan pembayaran akan tampil di halaman detail pesanan.';
                        hint.className = 'text-[11px] text-slate-400 mt-1 subtext';
                    }
                };
                select.addEventListener('change', updateHint);
                updateHint();
            }
        });

        function initCartSortables() {
            const secContainer = document.getElementById('cart-sections-container');
            if (secContainer && typeof Sortable !== 'undefined') {
                window.cartSectionsSortable = new Sortable(secContainer, {
                    animation: 250,
                    handle: '.cart-section-drag-handle',
                    draggable: '[data-cart-section]',
                    ghostClass: 'dashboard-sortable-ghost',
                    dragClass: 'dashboard-sortable-drag',
                    chosenClass: 'dashboard-sortable-chosen',
                    disabled: true,
                    onEnd: function() {
                        const newOrder = [];
                        secContainer.querySelectorAll('[data-cart-section]').forEach(el => {
                            newOrder.push(el.dataset.cartSection);
                        });
                        window.cartSectionsOrder = newOrder;
                    }
                });
            }
        }

        function initCartComponentStyles() {
            Object.keys(window.cartCurrentStyles).forEach(cId => {
                applyCartComponentStyle(cId, window.cartCurrentStyles[cId]);
            });
        }

        function toggleCartEditMode() {
            window.cartIsEditMode = !window.cartIsEditMode;
            const bar  = document.getElementById('cart-edit-mode-bar');
            const btn  = document.getElementById('cart-btn-text');
            const root = document.getElementById('caterflow-cart-root');

            if (window.cartIsEditMode) {
                bar.classList.remove('hidden');
                btn.textContent = '✕ Keluar Mode Penyesuaian';
                root.classList.add('in-edit-mode');
                document.querySelectorAll('.cart-edit-controls').forEach(el => el.classList.remove('hidden'));
                document.querySelectorAll('.cart-section-drag-handle').forEach(el => el.classList.remove('hidden'));
                document.querySelectorAll('[data-widget-wrapper]').forEach(el => {
                    el.classList.add('p-1.5', 'rounded-2xl', 'border-2', 'border-dashed', 'border-amber-500/50', 'bg-amber-50/10');
                });
            } else {
                bar.classList.add('hidden');
                btn.textContent = '⚙️ Sesuaikan Tampilan';
                root.classList.remove('in-edit-mode');
                document.querySelectorAll('.cart-edit-controls').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('.cart-section-drag-handle').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('[data-widget-wrapper]').forEach(el => {
                    el.classList.remove('p-1.5', 'rounded-2xl', 'border-2', 'border-dashed', 'border-amber-500/50', 'bg-amber-50/10');
                });
            }

            if (window.cartSectionsSortable) window.cartSectionsSortable.option('disabled', !window.cartIsEditMode);
            if (window.cartIsEditMode) updateCartStyleDisplays();
        }

        function setCartComponentShape(cId, shape) {
            if (!window.cartCurrentStyles[cId]) window.cartCurrentStyles[cId] = {};
            window.cartCurrentStyles[cId].shape = shape;
            applyCartComponentStyle(cId, window.cartCurrentStyles[cId]);

            document.querySelectorAll('.c-shape-btn-' + cId).forEach(btn => {
                const isActive = btn.dataset.shape === shape;
                btn.classList.toggle('bg-amber-500',   isActive);
                btn.classList.toggle('text-white',       isActive);
                btn.classList.toggle('border-amber-600', isActive);
                btn.classList.toggle('bg-white',        !isActive);
                btn.classList.toggle('text-slate-800',  !isActive);
                btn.classList.toggle('border-slate-300',!isActive);
            });
        }

        function onCartSliderInput(cId, dim, value) {
            const v = parseInt(value);
            if (isNaN(v)) return;
            if (!window.cartCurrentStyles[cId]) window.cartCurrentStyles[cId] = {};
            window.cartCurrentStyles[cId][dim] = v;
            applyCartComponentStyle(cId, window.cartCurrentStyles[cId]);
        }

        function applyCartComponentStyle(cId, style) {
            if (!style) return;
            const visualCard = document.querySelector('[data-component-id="' + cId + '"]');
            if (!visualCard) return;

            const defaultW = cId === 'C-CART-HEADER' ? 900 : (cId === 'C-CART-SUMMARY' ? 320 : 600);
            const defaultH = cId === 'C-CART-HEADER' ? 80 : (cId === 'C-CART-SUMMARY' ? 450 : (cId === 'C-CART-ITEMS' ? 300 : 140));
            const shape    = style.shape || 'rectangle';

            const reqWidth  = parseInt(style.width)  || defaultW;
            const reqHeight = parseInt(style.height) || defaultH;

            const bounds = { minW: 160, maxW: 900, minH: 60, maxH: 600 };
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

            // TRUE ADAPTIVE CONTENT RESIZING
            if (window.adaptComponentContent) {
                window.adaptComponentContent(visualCard, shape, clampedW, clampedH);
            }

            const actualRect = visualCard.getBoundingClientRect();
            const actualW    = Math.round(actualRect.width);
            const actualH    = Math.round(actualRect.height);

            const display = document.getElementById('c-size-display-' + cId);
            if (display) display.textContent = `${actualW} × ${actualH} px (Req: ${reqWidth}×${reqHeight})`;

            const wSlider = document.getElementById('c-width-slider-'  + cId);
            const hSlider = document.getElementById('c-height-slider-' + cId);
            const wVal    = document.getElementById('c-wval-'          + cId);
            const hVal    = document.getElementById('c-hval-'          + cId);

            if (wSlider) wSlider.value = reqWidth;
            if (hSlider) hSlider.value = reqHeight;
            if (wVal)    wVal.textContent = `${reqWidth}px`;
            if (hVal)    hVal.textContent = `${reqHeight}px`;
        }

        function updateCartStyleDisplays() {
            Object.keys(window.cartCurrentStyles).forEach(cId => {
                const style = window.cartCurrentStyles[cId];
                if (!style) return;

                const defaultW = cId === 'C-CART-HEADER' ? 900 : (cId === 'C-CART-SUMMARY' ? 320 : 600);
                const defaultH = cId === 'C-CART-HEADER' ? 80 : (cId === 'C-CART-SUMMARY' ? 450 : (cId === 'C-CART-ITEMS' ? 300 : 140));

                const w = style.width  || defaultW;
                const h = style.height || defaultH;

                const wSlider = document.getElementById('c-width-slider-'  + cId);
                const hSlider = document.getElementById('c-height-slider-' + cId);
                const wVal    = document.getElementById('c-wval-'          + cId);
                const hVal    = document.getElementById('c-hval-'          + cId);

                if (wSlider) wSlider.value = w;
                if (hSlider) hSlider.value = h;
                if (wVal)    wVal.textContent = `${w}px`;
                if (hVal)    hVal.textContent = `${h}px`;

                if (style.shape) setCartComponentShape(cId, style.shape);
            });
        }

        function saveCartLayout() {
            fetch('{{ route("workspace.save-layout") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ layout_matrix: {
                    cart_sections_order: window.cartSectionsOrder,
                    component_styles: { cart: JSON.parse(JSON.stringify(window.cartCurrentStyles)) }
                }})
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    window.cartLastSavedStyles   = JSON.parse(JSON.stringify(window.cartCurrentStyles));
                    window.cartSectionsLastSaved = [...window.cartSectionsOrder];
                    showCartToast('✓ Layout keranjang belanja berhasil disimpan.', 'success');
                    toggleCartEditMode();
                } else {
                    showCartToast('Gagal menyimpan layout: ' + (data.message || 'Error'), 'error');
                }
            })
            .catch(() => showCartToast('Kesalahan koneksi saat menyimpan layout.', 'error'));
        }

        function cancelCartLayout() {
            window.cartCurrentStyles = JSON.parse(JSON.stringify(window.cartLastSavedStyles));
            window.cartSectionsOrder = [...window.cartSectionsLastSaved];

            // Reorder sections in DOM
            const secContainer = document.getElementById('cart-sections-container');
            if (secContainer && window.cartSectionsOrder.length) {
                const secs = {};
                secContainer.querySelectorAll('[data-cart-section]').forEach(s => { secs[s.dataset.cartSection] = s; });
                window.cartSectionsOrder.forEach(secId => { if (secs[secId]) secContainer.appendChild(secs[secId]); });
            }

            Object.keys(window.cartCurrentStyles).forEach(cId => applyCartComponentStyle(cId, window.cartCurrentStyles[cId]));
            showCartToast('Perubahan dibatalkan.', 'info');
            toggleCartEditMode();
        }

        function resetCartLayout() {
            if (!confirm('Kembalikan tampilan keranjang ke susunan default sistem?')) return;
            fetch('{{ route("workspace.reset-layout") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ page: 'cart' })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const defaultSecOrder = data.layout_matrix?.cart_sections_order || ['C-CART-HEADER', 'C-CART-ITEMS', 'C-CART-GUIDE', 'C-CART-SUMMARY'];
                    window.cartSectionsOrder    = [...defaultSecOrder];
                    window.cartSectionsLastSaved= [...defaultSecOrder];

                    const resetStyles = (data.layout_matrix?.component_styles || {}).cart || window.cartDefaultStyles;
                    window.cartCurrentStyles   = Object.assign({}, resetStyles);
                    window.cartLastSavedStyles = JSON.parse(JSON.stringify(window.cartCurrentStyles));

                    // Reorder sections in DOM
                    const secContainer = document.getElementById('cart-sections-container');
                    if (secContainer && window.cartSectionsOrder.length) {
                        const secs = {};
                        secContainer.querySelectorAll('[data-cart-section]').forEach(s => { secs[s.dataset.cartSection] = s; });
                        window.cartSectionsOrder.forEach(secId => { if (secs[secId]) secContainer.appendChild(secs[secId]); });
                    }

                    Object.keys(window.cartCurrentStyles).forEach(cId => applyCartComponentStyle(cId, window.cartCurrentStyles[cId]));
                    showCartToast('✓ Layout keranjang dikembalikan ke default.', 'success');
                    if (window.cartIsEditMode) toggleCartEditMode();
                }
            })
            .catch(() => showCartToast('Gagal melakukan reset layout.', 'error'));
        }

        function resetSingleCartComponent(cId) {
            const defaultW = cId === 'C-CART-HEADER' ? 900 : (cId === 'C-CART-SUMMARY' ? 320 : 600);
            const defaultH = cId === 'C-CART-HEADER' ? 80 : (cId === 'C-CART-SUMMARY' ? 450 : (cId === 'C-CART-ITEMS' ? 300 : 140));
            const defaults = (window.cartDefaultStyles && window.cartDefaultStyles[cId]) || { shape: 'rectangle', width: defaultW, height: defaultH, border_radius: 16 };
            window.cartCurrentStyles[cId] = Object.assign({}, defaults);
            applyCartComponentStyle(cId, window.cartCurrentStyles[cId]);
            updateCartStyleDisplays();
            showCartToast('✓ Komponen ' + cId + ' dikembalikan ke default.', 'success');
        }

        function showCartToast(msg, type = 'success') {
            const toast = document.getElementById('cart-toast');
            const msgEl = document.getElementById('cart-toast-message');
            const icon  = document.getElementById('cart-toast-icon');
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

