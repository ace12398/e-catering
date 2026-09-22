<x-dashboard-layout>
    <x-slot name="title">Profil Saya — E-Catering</x-slot>
    <x-slot name="toolbarTitle">Profil Saya</x-slot>

    {{-- SortableJS CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

    @php
        $effectiveMatrix = $layoutMatrix ?? $layout_matrix ?? auth()->user()?->preference?->effective_layout_matrix ?? \App\Models\WorkspacePreference::getDefaultLayoutMatrix();
        $profileSavedStyles = $profileStyles ?? $effectiveMatrix['component_styles']['profile'] ?? [];
        $defaultProfileStyles = \App\Models\WorkspacePreference::getDefaultLayoutMatrix()['component_styles']['profile'];
        $profileSidebarOrder = $profileSidebarOrder ?? $effectiveMatrix['profile_sidebar_order'] ?? ['P-COMPLETION', 'P-WORKSPACE', 'P-ACTIVITY'];

        $sidebarCardsData = [
            'P-COMPLETION' => [
                'id'      => 'P-COMPLETION',
                'title'   => 'Kelengkapan Profil',
                'view'    => 'profile.partials.completion-card',
                'defaultW'=> 320,
                'defaultH'=> 240,
                'minW'    => 160,
                'maxW'    => 500,
                'minH'    => 100,
                'maxH'    => 400,
            ],
            'P-WORKSPACE' => [
                'id'      => 'P-WORKSPACE',
                'title'   => 'Pengaturan Beranda',
                'view'    => 'profile.partials.workspace-card',
                'defaultW'=> 320,
                'defaultH'=> 160,
                'minW'    => 160,
                'maxW'    => 500,
                'minH'    => 80,
                'maxH'    => 350,
            ],
            'P-ACTIVITY' => [
                'id'      => 'P-ACTIVITY',
                'title'   => 'Riwayat Aktivitas',
                'view'    => 'profile.partials.activity-card',
                'defaultW'=> 320,
                'defaultH'=> 200,
                'minW'    => 160,
                'maxW'    => 500,
                'minH'    => 100,
                'maxH'    => 400,
            ],
        ];

        $sortedSidebarCards = [];
        foreach ($profileSidebarOrder as $cId) {
            if (isset($sidebarCardsData[$cId])) $sortedSidebarCards[] = $sidebarCardsData[$cId];
        }
        foreach ($sidebarCardsData as $cId => $cData) {
            if (!in_array($cId, $profileSidebarOrder)) $sortedSidebarCards[] = $cData;
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

    <div class="max-w-6xl mx-auto space-y-6 relative" id="caterflow-profile-root">

        {{-- TOAST NOTIFICATION --}}
        <div id="profile-toast" class="hidden fixed bottom-6 right-6 z-50 p-4 rounded-xl bg-slate-900 text-white shadow-2xl border border-slate-700 flex items-center space-x-3 transition-all transform translate-y-4 opacity-0">
            <span id="profile-toast-icon" class="text-amber-400 text-lg font-bold">✓</span>
            <span id="profile-toast-message" class="text-xs font-medium">Layout profil berhasil diperbarui.</span>
        </div>

        {{-- CANONICAL ADAPTIVE MODE BANNER (MATCHING /finance & /dashboard) --}}
        <div id="profile-edit-mode-bar" class="hidden sticky top-4 z-40 p-4 rounded-2xl bg-amber-950/90 dark:bg-amber-950/95 backdrop-blur border-2 border-amber-500 text-white shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center space-x-3">
                <span class="p-2.5 rounded-xl bg-amber-500/20 border border-amber-400/30 text-amber-300 font-mono text-base">✨</span>
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-300">Mode Penyesuaian Tampilan Aktif — Profil Saya</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500 text-slate-950">Drag & Drop Aktif</span>
                    </div>
                    <p class="text-xs text-amber-100/90 mt-0.5">Ubah bentuk (shape), lebar, tinggi, dan geser kartu informasi profil secara live.</p>
                </div>
            </div>
            <div class="flex items-center space-x-2 shrink-0">
                <button type="button" onclick="cancelProfileLayout()" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs transition-colors border border-slate-600">
                    ✕ Batal
                </button>
                <button type="button" onclick="resetProfileLayout()" class="px-3.5 py-2 rounded-xl bg-amber-900/80 hover:bg-amber-800 text-amber-200 font-semibold text-xs transition-colors border border-amber-600">
                    🔄 Reset Default
                </button>
                <button type="button" onclick="saveProfileLayout()" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold text-xs shadow-md transition-colors flex items-center space-x-1.5">
                    <span>💾 Simpan Tampilan</span>
                </button>
            </div>
        </div>

        {{-- 1. ADAPTIVE HEADER COMPONENT (Component ID: P-HEADER) --}}
        <div id="profile-wrapper-P-HEADER" class="w-full flex flex-col items-center space-y-3 transition-all">
            {{-- BUSINESS PREVIEW CARD FOR HEADER --}}
            <div id="profile-card-P-HEADER" data-component-id="P-HEADER"
                 class="caterflow-visual-card w-full rounded-2xl transition-all relative overflow-hidden">
                <div class="relative">
                    <div class="absolute top-4 right-4 z-10">
                        <button type="button" id="profile-btn-edit" onclick="toggleProfileEditMode()"
                                class="h-9 px-4 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs transition-colors flex items-center space-x-2 shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            <span id="profile-btn-text">⚙️ Sesuaikan Tampilan</span>
                        </button>
                    </div>
                    <x-profile.adaptive-header :user="$user" />
                </div>
            </div>

            {{-- SEPARATE RECTANGULAR CONFIGURATION EDITOR (OUTSIDE PREVIEW) --}}
            <div class="profile-edit-controls hidden w-full p-3.5 rounded-xl bg-slate-900/90 text-white border border-slate-700/60 shadow-lg space-y-2.5 text-xs backdrop-blur z-20">
                <div class="flex items-center justify-between font-bold border-b border-slate-700 pb-1.5 text-[10px]">
                    <span class="text-amber-400 font-bold truncate">⚙️ Atur Komponen: Header Profil (P-HEADER)</span>
                    <button type="button" onclick="resetSingleProfileComponent('P-HEADER')" class="px-1.5 py-0.5 rounded text-[9px] font-semibold bg-amber-800/80 hover:bg-amber-700 text-white border border-amber-600 transition-colors" title="Reset komponen ini">🔄 Reset</button>
                </div>
                <div class="space-y-1">
                    <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Bentuk</span>
                    <div class="grid grid-cols-3 sm:grid-cols-6 gap-1">
                        @foreach(['rectangle'=>'■ Kotak','rounded'=>'◉ Bulat','sharp'=>'▪ Tajam','pill'=>'⬭ Pil','circle'=>'○ Lingkar','hexagon'=>'⬡ Hex'] as $sId => $sLabel)
                            <button type="button" onclick="setProfileComponentShape('P-HEADER', '{{ $sId }}')" class="p-shape-btn-P-HEADER px-1 py-1 rounded text-[9px] font-semibold border text-center transition-colors bg-white text-slate-800 border-slate-300 hover:bg-amber-500 hover:text-white" data-shape="{{ $sId }}">{{ $sLabel }}</button>
                        @endforeach
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <div class="flex items-center justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                            <span>Lebar</span>
                            <span id="p-wval-P-HEADER" class="text-amber-400 font-mono font-bold">900px</span>
                        </div>
                        <input type="range" min="240" max="900" step="10" value="900" id="p-width-slider-P-HEADER" oninput="onProfileSliderInput('P-HEADER', 'width', this.value)" class="w-full accent-amber-500">
                    </div>
                    <div>
                        <div class="flex items-center justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                            <span>Tinggi</span>
                            <span id="p-hval-P-HEADER" class="text-amber-400 font-mono font-bold">120px</span>
                        </div>
                        <input type="range" min="80" max="400" step="10" value="120" id="p-height-slider-P-HEADER" oninput="onProfileSliderInput('P-HEADER', 'height', this.value)" class="w-full accent-amber-500">
                    </div>
                </div>
                <div class="text-[9px] font-mono text-amber-400 pt-1 border-t border-slate-700/60" id="p-size-display-P-HEADER">900 × 120 px</div>
            </div>
        </div>

        {{-- 2. MAIN WORKSPACE GRID --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            
            {{-- LEFT COLUMN: MAIN PROFILE FORM COMPONENT (P-FORM) --}}
            <div class="lg:col-span-2 space-y-6">
                <div id="profile-wrapper-P-FORM" class="w-full flex flex-col items-center space-y-3 transition-all">
                    
                    {{-- BUSINESS PREVIEW CARD FOR FORM --}}
                    <div id="profile-card-P-FORM" data-component-id="P-FORM"
                         class="caterflow-visual-card w-full p-6 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6 transition-all relative overflow-hidden">
                        
                        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 flex-between-header">
                            <h3 class="text-base font-bold text-slate-900 dark:text-white adaptive-label">
                                Informasi Profil Pengguna
                            </h3>
                            @if($user->profile?->avatar)
                                <form method="POST" action="{{ route('profile.delete-avatar') }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Yakin ingin menghapus foto profil?')" 
                                            class="px-3 py-1 rounded-lg text-xs font-semibold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 transition-colors border border-rose-200 dark:border-rose-800">
                                        🗑️ Hapus Foto Profil
                                    </button>
                                </form>
                            @endif
                        </div>

                        @if (session('success') || session('status') === 'profile-updated' || session('status') === 'avatar-uploaded' || session('status') === 'avatar-deleted')
                            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-bold flex items-center space-x-2">
                                <span>✅</span>
                                <span>{{ session('success') ?? 'Informasi profil berhasil diperbarui.' }}</span>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs font-semibold space-y-1">
                                @foreach ($errors->all() as $error)
                                    <p>• {{ $error }}</p>
                                @endforeach
                            </div>
                        @endif

                        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-5">
                            @csrf
                            @method('PATCH')

                            <!-- AVATAR PREVIEW & UPLOAD SECTION -->
                            <div class="flex items-center space-x-5 p-4 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                                <img src="{{ $user->profile?->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) }}" 
                                     alt="{{ $user->name }}" 
                                     class="w-16 h-16 rounded-full object-cover border-2 border-emerald-500 shadow-sm shrink-0 adaptive-img">
                                <div class="space-y-1 flex-1">
                                    <label class="block text-xs font-bold text-slate-900 dark:text-white uppercase adaptive-label">Foto Profil (Maksimal 2MB)</label>
                                    <input type="file" name="avatar" accept="image/*"
                                           class="w-full px-3 py-1.5 text-xs rounded-xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300">
                                    <p class="text-[11px] text-slate-400 subtext">Format yang diperbolehkan: JPG, JPEG, PNG, WEBP.</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Name -->
                                <div class="space-y-1">
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase">Nama Lengkap</label>
                                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:border-emerald-500">
                                </div>

                                <!-- Company -->
                                <div class="space-y-1">
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase">Nama Perusahaan</label>
                                    <input type="text" name="company_name" value="{{ old('company_name', $user->profile?->company_name) }}"
                                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:border-emerald-500">
                                </div>

                                <!-- Phone -->
                                <div class="space-y-1">
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase">Nomor Telepon / WhatsApp</label>
                                    <input type="text" name="phone_number" value="{{ old('phone_number', $user->profile?->phone_number) }}"
                                           class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:border-emerald-500">
                                </div>

                                <!-- Username (Readonly) -->
                                <div class="space-y-1">
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase">Username Sistem</label>
                                    <input type="text" value="{{ $user->username }}" disabled
                                           class="w-full px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500 text-sm cursor-not-allowed">
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="space-y-1">
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase">Alamat Pengiriman Utama</label>
                                <textarea name="address" rows="3"
                                          class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:border-emerald-500">{{ old('address', $user->profile?->address) }}</textarea>
                            </div>

                            <div class="flex justify-end pt-2">
                                <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 text-white font-semibold text-xs hover:bg-emerald-500 transition-colors shadow-sm">
                                    Simpan Perubahan Profil 💾
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- SEPARATE RECTANGULAR CONFIGURATION EDITOR (OUTSIDE PREVIEW) --}}
                    <div class="profile-edit-controls hidden w-full p-3.5 rounded-xl bg-slate-900/90 text-white border border-slate-700/60 shadow-lg space-y-2.5 text-xs backdrop-blur z-20">
                        <div class="flex items-center justify-between font-bold border-b border-slate-700 pb-1.5 text-[10px]">
                            <span class="text-amber-400 font-bold truncate">⚙️ Atur Komponen: Formulir Informasi Profil (P-FORM)</span>
                            <button type="button" onclick="resetSingleProfileComponent('P-FORM')" class="px-1.5 py-0.5 rounded text-[9px] font-semibold bg-amber-800/80 hover:bg-amber-700 text-white border border-amber-600 transition-colors" title="Reset komponen ini">🔄 Reset</button>
                        </div>
                        <div class="space-y-1">
                            <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Bentuk</span>
                            <div class="grid grid-cols-3 sm:grid-cols-6 gap-1">
                                @foreach(['rectangle'=>'■ Kotak','rounded'=>'◉ Bulat','sharp'=>'▪ Tajam','pill'=>'⬭ Pil','circle'=>'○ Lingkar','hexagon'=>'⬡ Hex'] as $sId => $sLabel)
                                    <button type="button" onclick="setProfileComponentShape('P-FORM', '{{ $sId }}')" class="p-shape-btn-P-FORM px-1 py-1 rounded text-[9px] font-semibold border text-center transition-colors bg-white text-slate-800 border-slate-300 hover:bg-amber-500 hover:text-white" data-shape="{{ $sId }}">{{ $sLabel }}</button>
                                @endforeach
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <div class="flex items-center justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                                    <span>Lebar</span>
                                    <span id="p-wval-P-FORM" class="text-amber-400 font-mono font-bold">600px</span>
                                </div>
                                <input type="range" min="240" max="800" step="10" value="600" id="p-width-slider-P-FORM" oninput="onProfileSliderInput('P-FORM', 'width', this.value)" class="w-full accent-amber-500">
                            </div>
                            <div>
                                <div class="flex items-center justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                                    <span>Tinggi</span>
                                    <span id="p-hval-P-FORM" class="text-amber-400 font-mono font-bold">420px</span>
                                </div>
                                <input type="range" min="150" max="650" step="10" value="420" id="p-height-slider-P-FORM" oninput="onProfileSliderInput('P-FORM', 'height', this.value)" class="w-full accent-amber-500">
                            </div>
                        </div>
                        <div class="text-[9px] font-mono text-amber-400 pt-1 border-t border-slate-700/60" id="p-size-display-P-FORM">600 × 420 px</div>
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN: SIDEBAR CARDS SORTABLE CONTAINER --}}
            <div class="lg:col-span-1 space-y-6" id="profile-sidebar-container">
                @foreach($sortedSidebarCards as $card)
                    @php $cardId = $card['id']; @endphp
                    <div data-profile-card="{{ $cardId }}" class="w-full flex flex-col items-center space-y-3 transition-all">
                        
                        {{-- BUSINESS PREVIEW CARD FOR SIDEBAR ITEM --}}
                        <div id="profile-card-{{ $cardId }}" data-component-id="{{ $cardId }}"
                             class="caterflow-visual-card w-full rounded-2xl transition-all relative overflow-hidden">
                            
                            {{-- Dedicated Drag Handle Badge (Edit Mode Only) --}}
                            <div class="profile-sidebar-drag-handle hidden absolute top-3 right-3 z-10 px-2 py-0.5 rounded-md bg-amber-500/20 hover:bg-amber-500 text-amber-800 dark:text-amber-200 hover:text-white border border-amber-400/40 cursor-grab active:cursor-grabbing transition-colors flex items-center space-x-1 select-none shadow-sm"
                                 title="Tahan dan geser untuk memindahkan urutan kartu sidebar">
                                <svg class="w-3 h-3 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 8h16M4 16h16"></path>
                                </svg>
                                <span class="text-[9px] font-extrabold font-mono pointer-events-none">GESER</span>
                            </div>

                            @include($card['view'], ['user' => $user])
                        </div>

                        {{-- SEPARATE RECTANGULAR CONFIGURATION EDITOR (OUTSIDE PREVIEW) --}}
                        <div class="profile-edit-controls hidden w-full p-3.5 rounded-xl bg-slate-900/90 text-white border border-slate-700/60 shadow-lg space-y-2.5 text-xs backdrop-blur z-20">
                            <div class="flex items-center justify-between font-bold border-b border-slate-700 pb-1.5 text-[10px]">
                                <div class="flex items-center space-x-1.5">
                                    <span class="profile-sidebar-drag-handle cursor-grab active:cursor-grabbing px-2 py-0.5 rounded bg-amber-600 hover:bg-amber-500 text-white text-[10px] font-bold flex items-center space-x-1 shadow-sm transition-colors select-none"
                                          title="Tahan dan geser untuk memindahkan urutan">
                                        <span>⠿</span>
                                        <span>Geser</span>
                                    </span>
                                    <span class="text-amber-400 font-bold truncate">Atur: {{ $card['title'] }}</span>
                                </div>
                                <button type="button" onclick="resetSingleProfileComponent('{{ $cardId }}')" class="px-1.5 py-0.5 rounded text-[9px] font-semibold bg-amber-800/80 hover:bg-amber-700 text-white border border-amber-600 transition-colors" title="Reset komponen ini">🔄 Reset</button>
                            </div>
                            <div class="space-y-1">
                                <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Bentuk</span>
                                <div class="grid grid-cols-3 gap-1">
                                    @foreach(['rectangle'=>'■ Kotak','rounded'=>'◉ Bulat','sharp'=>'▪ Tajam','pill'=>'⬭ Pil','circle'=>'○ Lingkar','hexagon'=>'⬡ Hex'] as $sId => $sLabel)
                                        <button type="button" onclick="setProfileComponentShape('{{ $cardId }}', '{{ $sId }}')" class="p-shape-btn-{{ $cardId }} px-1 py-1 rounded text-[9px] font-semibold border text-center transition-colors bg-white text-slate-800 border-slate-300 hover:bg-amber-500 hover:text-white" data-shape="{{ $sId }}">{{ $sLabel }}</button>
                                    @endforeach
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-1.5">
                                <div>
                                    <div class="flex items-center justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                                        <span>Lebar</span>
                                        <span id="p-wval-{{ $cardId }}" class="text-amber-400 font-mono font-bold">{{ $card['defaultW'] }}px</span>
                                    </div>
                                    <input type="range" min="{{ $card['minW'] }}" max="{{ $card['maxW'] }}" step="10" value="{{ $card['defaultW'] }}" id="p-width-slider-{{ $cardId }}" oninput="onProfileSliderInput('{{ $cardId }}', 'width', this.value)" class="w-full accent-amber-500">
                                </div>
                                <div>
                                    <div class="flex items-center justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                                        <span>Tinggi</span>
                                        <span id="p-hval-{{ $cardId }}" class="text-amber-400 font-mono font-bold">{{ $card['defaultH'] }}px</span>
                                    </div>
                                    <input type="range" min="{{ $card['minH'] }}" max="{{ $card['maxH'] }}" step="10" value="{{ $card['defaultH'] }}" id="p-height-slider-{{ $cardId }}" oninput="onProfileSliderInput('{{ $cardId }}', 'height', this.value)" class="w-full accent-amber-500">
                                </div>
                            </div>
                            <div class="text-[9px] font-mono text-amber-400 pt-1 border-t border-slate-700/60" id="p-size-display-{{ $cardId }}">{{ $card['defaultW'] }} × {{ $card['defaultH'] }} px</div>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>

    </div>

    {{-- ADAPTIVE ENGINE SCRIPT FOR PROFILE PAGE --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.profileIsEditMode        = false;
            window.profileSidebarOrder      = @json($profileSidebarOrder);
            window.profileSidebarLastSaved  = [...window.profileSidebarOrder];
            window.profileSavedStyles       = @json($profileSavedStyles);
            window.profileDefaults          = @json($defaultProfileStyles);
            window.profileCurrentStyles     = Object.assign({}, window.profileDefaults, window.profileSavedStyles);
            window.profileLastSavedStyles   = JSON.parse(JSON.stringify(window.profileCurrentStyles));
            window.profileSidebarSortable   = null;

            initProfileSortables();
            initProfileComponentStyles();
        });

        function initProfileSortables() {
            const sidebarContainer = document.getElementById('profile-sidebar-container');
            if (sidebarContainer) {
                window.profileSidebarSortable = new Sortable(sidebarContainer, {
                    animation: 200,
                    handle: '.profile-sidebar-drag-handle',
                    ghostClass: 'dashboard-sortable-ghost',
                    dragClass: 'dashboard-sortable-drag',
                    chosenClass: 'dashboard-sortable-chosen',
                    disabled: true,
                    onEnd: function() {
                        const newOrder = [];
                        sidebarContainer.querySelectorAll('[data-profile-card]').forEach(el => newOrder.push(el.dataset.profileCard));
                        window.profileSidebarOrder = newOrder;
                    }
                });
            }
        }

        function initProfileComponentStyles() {
            Object.keys(window.profileCurrentStyles).forEach(cId => {
                applyProfileComponentStyle(cId, window.profileCurrentStyles[cId]);
            });
        }

        function toggleProfileEditMode() {
            window.profileIsEditMode = !window.profileIsEditMode;
            const bar  = document.getElementById('profile-edit-mode-bar');
            const btn  = document.getElementById('profile-btn-text');
            const root = document.getElementById('caterflow-profile-root');

            if (window.profileIsEditMode) {
                bar.classList.remove('hidden');
                btn.textContent = '✕ Keluar Mode Penyesuaian';
                root.classList.add('in-edit-mode');
                document.querySelectorAll('.profile-edit-controls').forEach(el => el.classList.remove('hidden'));
                document.querySelectorAll('.profile-sidebar-drag-handle').forEach(el => el.classList.remove('hidden'));
                document.querySelectorAll('#profile-wrapper-P-HEADER, #profile-wrapper-P-FORM, [data-profile-card]').forEach(el => {
                    el.classList.add('p-1.5', 'rounded-2xl', 'border-2', 'border-dashed', 'border-amber-500/50', 'bg-amber-50/10');
                });
            } else {
                bar.classList.add('hidden');
                btn.textContent = '⚙️ Sesuaikan Tampilan';
                root.classList.remove('in-edit-mode');
                document.querySelectorAll('.profile-edit-controls').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('.profile-sidebar-drag-handle').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('#profile-wrapper-P-HEADER, #profile-wrapper-P-FORM, [data-profile-card]').forEach(el => {
                    el.classList.remove('p-1.5', 'rounded-2xl', 'border-2', 'border-dashed', 'border-amber-500/50', 'bg-amber-50/10');
                });
            }

            if (window.profileSidebarSortable) window.profileSidebarSortable.option('disabled', !window.profileIsEditMode);
            if (window.profileIsEditMode) updateProfileStyleDisplays();
        }

        function setProfileComponentShape(cId, shape) {
            if (!window.profileCurrentStyles[cId]) window.profileCurrentStyles[cId] = {};
            window.profileCurrentStyles[cId].shape = shape;
            applyProfileComponentStyle(cId, window.profileCurrentStyles[cId]);

            document.querySelectorAll('.p-shape-btn-' + cId).forEach(btn => {
                const isActive = btn.dataset.shape === shape;
                btn.classList.toggle('bg-amber-500',   isActive);
                btn.classList.toggle('text-white',       isActive);
                btn.classList.toggle('border-amber-600', isActive);
                btn.classList.toggle('bg-white',        !isActive);
                btn.classList.toggle('text-slate-800',  !isActive);
                btn.classList.toggle('border-slate-300',!isActive);
            });
        }

        function onProfileSliderInput(cId, dim, value) {
            const v = parseInt(value);
            if (isNaN(v)) return;
            if (!window.profileCurrentStyles[cId]) window.profileCurrentStyles[cId] = {};
            window.profileCurrentStyles[cId][dim] = v;
            applyProfileComponentStyle(cId, window.profileCurrentStyles[cId]);
        }

        function applyProfileComponentStyle(cId, style) {
            if (!style) return;
            const visualCard = document.querySelector('[data-component-id="' + cId + '"]');
            if (!visualCard) return;

            let defaultW = 320, defaultH = 200;
            let bounds   = { minW: 160, maxW: 500, minH: 80, maxH: 400 };

            if (cId === 'P-HEADER') {
                defaultW = 900; defaultH = 120;
                bounds   = { minW: 240, maxW: 900, minH: 80, maxH: 400 };
            } else if (cId === 'P-FORM') {
                defaultW = 600; defaultH = 420;
                bounds   = { minW: 240, maxW: 800, minH: 150, maxH: 650 };
            }

            const reqWidth  = parseInt(style.width)  || defaultW;
            const reqHeight = parseInt(style.height) || defaultH;
            const shape     = style.shape || 'rectangle';

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

            const display = document.getElementById('p-size-display-' + cId);
            if (display) display.textContent = `${actualW} × ${actualH} px (Req: ${reqWidth}×${reqHeight})`;

            const wSlider = document.getElementById('p-width-slider-'  + cId);
            const hSlider = document.getElementById('p-height-slider-' + cId);
            const wVal    = document.getElementById('p-wval-'          + cId);
            const hVal    = document.getElementById('p-hval-'          + cId);

            if (wSlider) wSlider.value = reqWidth;
            if (hSlider) hSlider.value = reqHeight;
            if (wVal)    wVal.textContent = `${reqWidth}px`;
            if (hVal)    hVal.textContent = `${reqHeight}px`;
        }

        function updateProfileStyleDisplays() {
            Object.keys(window.profileCurrentStyles).forEach(cId => {
                const style = window.profileCurrentStyles[cId];
                if (!style) return;

                let defaultW = 320, defaultH = 200;
                if (cId === 'P-HEADER') { defaultW = 900; defaultH = 120; }
                else if (cId === 'P-FORM') { defaultW = 600; defaultH = 420; }

                const w = style.width  || defaultW;
                const h = style.height || defaultH;

                const wSlider = document.getElementById('p-width-slider-'  + cId);
                const hSlider = document.getElementById('p-height-slider-' + cId);
                const wVal    = document.getElementById('p-wval-'          + cId);
                const hVal    = document.getElementById('p-hval-'          + cId);

                if (wSlider) wSlider.value = w;
                if (hSlider) hSlider.value = h;
                if (wVal)    wVal.textContent = `${w}px`;
                if (hVal)    hVal.textContent = `${h}px`;

                if (style.shape) setProfileComponentShape(cId, style.shape);
            });
        }

        function saveProfileLayout() {
            fetch('{{ route("workspace.save-layout") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ layout_matrix: {
                    profile_sidebar_order: window.profileSidebarOrder,
                    component_styles: { profile: JSON.parse(JSON.stringify(window.profileCurrentStyles)) }
                }})
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    window.profileLastSavedStyles  = JSON.parse(JSON.stringify(window.profileCurrentStyles));
                    window.profileSidebarLastSaved = [...window.profileSidebarOrder];
                    showProfileToast('✓ Urutan & tampilan profil berhasil disimpan.', 'success');
                    toggleProfileEditMode();
                } else {
                    showProfileToast('Gagal menyimpan: ' + (data.message || 'Error'), 'error');
                }
            })
            .catch(() => showProfileToast('Kesalahan koneksi saat menyimpan layout.', 'error'));
        }

        function cancelProfileLayout() {
            window.profileCurrentStyles = JSON.parse(JSON.stringify(window.profileLastSavedStyles));
            window.profileSidebarOrder  = [...window.profileSidebarLastSaved];

            // Reorder sidebar cards in DOM
            const sidebarContainer = document.getElementById('profile-sidebar-container');
            if (sidebarContainer && window.profileSidebarOrder.length) {
                const wrappers = {};
                sidebarContainer.querySelectorAll('[data-profile-card]').forEach(w => { wrappers[w.dataset.profileCard] = w; });
                window.profileSidebarOrder.forEach(cId => { if (wrappers[cId]) sidebarContainer.appendChild(wrappers[cId]); });
            }

            Object.keys(window.profileCurrentStyles).forEach(cId => applyProfileComponentStyle(cId, window.profileCurrentStyles[cId]));
            showProfileToast('Perubahan dibatalkan.', 'info');
            toggleProfileEditMode();
        }

        function resetProfileLayout() {
            if (!confirm('Kembalikan seluruh tampilan profil ke susunan default?')) return;
            fetch('{{ route("workspace.reset-layout") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ page: 'profile' })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const defaultSidebarOrder = data.layout_matrix?.profile_sidebar_order || ['P-COMPLETION', 'P-WORKSPACE', 'P-ACTIVITY'];
                    window.profileSidebarOrder      = [...defaultSidebarOrder];
                    window.profileSidebarLastSaved  = [...defaultSidebarOrder];

                    const resetStyles = (data.layout_matrix?.component_styles || {}).profile || window.profileDefaults;
                    window.profileCurrentStyles     = Object.assign({}, resetStyles);
                    window.profileLastSavedStyles   = JSON.parse(JSON.stringify(window.profileCurrentStyles));

                    // Reorder sidebar cards in DOM
                    const sidebarContainer = document.getElementById('profile-sidebar-container');
                    if (sidebarContainer && window.profileSidebarOrder.length) {
                        const wrappers = {};
                        sidebarContainer.querySelectorAll('[data-profile-card]').forEach(w => { wrappers[w.dataset.profileCard] = w; });
                        window.profileSidebarOrder.forEach(cId => { if (wrappers[cId]) sidebarContainer.appendChild(wrappers[cId]); });
                    }

                    Object.keys(window.profileCurrentStyles).forEach(cId => applyProfileComponentStyle(cId, window.profileCurrentStyles[cId]));
                    showProfileToast('✓ Seluruh tampilan profil dikembalikan ke default.', 'success');
                    if (window.profileIsEditMode) toggleProfileEditMode();
                }
            })
            .catch(() => showProfileToast('Gagal melakukan reset layout.', 'error'));
        }

        function resetSingleProfileComponent(cId) {
            let defaults = { shape: 'rectangle', width: 320, height: 200, border_radius: 16 };
            if (cId === 'P-HEADER') defaults = { shape: 'rectangle', width: 900, height: 120, border_radius: 16 };
            else if (cId === 'P-FORM') defaults = { shape: 'rectangle', width: 600, height: 420, border_radius: 16 };
            else if (window.profileDefaults && window.profileDefaults[cId]) defaults = window.profileDefaults[cId];

            window.profileCurrentStyles[cId] = Object.assign({}, defaults);
            applyProfileComponentStyle(cId, window.profileCurrentStyles[cId]);
            updateProfileStyleDisplays();
            showProfileToast('✓ Komponen ' + cId + ' dikembalikan ke default.', 'success');
        }

        function showProfileToast(msg, type = 'success') {
            const toast = document.getElementById('profile-toast');
            const msgEl = document.getElementById('profile-toast-message');
            const icon  = document.getElementById('profile-toast-icon');
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
