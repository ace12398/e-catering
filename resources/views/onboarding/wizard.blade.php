<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50 dark:bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pengaturan Panduan Awal — E-Catering</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 antialiased font-sans flex items-center justify-center" x-data="{ step: 1 }">
    <div class="w-full max-w-2xl mx-auto px-6">
        {{-- Header --}}
        <div class="text-center mb-8">
            <div class="w-14 h-14 rounded-2xl bg-emerald-600 flex items-center justify-center font-bold text-white shadow-md shadow-emerald-600/20 mx-auto mb-4 text-xl">EC</div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Selamat Datang di E-Catering</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Mari sesuaikan antarmuka sesuai kebutuhan Anda. Proses ini hanya membutuhkan 30 detik.</p>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1 italic">Metode UCD (ISO 9241-210) — Penyesuaian Konteks Penggunaan</p>
        </div>

        {{-- Progress --}}
        <div class="flex items-center justify-center space-x-2 mb-8">
            <template x-for="s in 3">
                <div class="h-1.5 rounded-full transition-all duration-300" :class="step >= s ? 'w-16 bg-emerald-600' : 'w-8 bg-slate-300 dark:bg-slate-700'"></div>
            </template>
        </div>

        <form method="POST" action="{{ route('onboarding.store') }}" class="p-8 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
            @csrf

            {{-- STEP 1: Goal --}}
            <div x-show="step === 1" x-transition>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-1">Apa tujuan utama penggunaan aplikasi Anda?</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-6">Pilihan ini membantu menyesuaikan beranda dan navigasi utama Anda.</p>
                <div class="space-y-3">
                    <label class="flex items-center p-4 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer hover:border-emerald-500 transition-colors has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 has-[:checked]:ring-2 has-[:checked]:ring-emerald-500/20">
                        <input type="radio" name="goal" value="ordering" class="text-emerald-600 focus:ring-emerald-500" checked>
                        <div class="ml-3">
                            <span class="text-sm font-bold text-slate-900 dark:text-white">🛒 Pemesanan Katering</span>
                            <p class="text-[11px] text-slate-500">Saya ingin menjelajahi menu, membuat pesanan, dan melacak pengiriman.</p>
                        </div>
                    </label>
                    <label class="flex items-center p-4 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer hover:border-emerald-500 transition-colors has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 has-[:checked]:ring-2 has-[:checked]:ring-emerald-500/20">
                        <input type="radio" name="goal" value="managing" class="text-emerald-600 focus:ring-emerald-500">
                        <div class="ml-3">
                            <span class="text-sm font-bold text-slate-900 dark:text-white">📊 Manajemen Operasional</span>
                            <p class="text-[11px] text-slate-500">Saya mengelola dapur, pengiriman, dan keuangan katering.</p>
                        </div>
                    </label>
                    <label class="flex items-center p-4 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer hover:border-emerald-500 transition-colors has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 has-[:checked]:ring-2 has-[:checked]:ring-emerald-500/20">
                        <input type="radio" name="goal" value="both" class="text-emerald-600 focus:ring-emerald-500">
                        <div class="ml-3">
                            <span class="text-sm font-bold text-slate-900 dark:text-white">🔄 Kedua Aktivitas</span>
                            <p class="text-[11px] text-slate-500">Saya melakukan pemesanan katering sekaligus mengelola operasional.</p>
                        </div>
                    </label>
                </div>
                <div class="flex justify-end pt-6">
                    <button type="button" @click="step = 2" class="px-6 py-2.5 rounded-xl bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-500 transition-colors">Lanjut →</button>
                </div>
            </div>

            {{-- STEP 2: Workspace Preset --}}
            <div x-show="step === 2" x-transition>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-1">Pilih tata letak tampilan</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-6">Anda dapat mengubah pilihan ini kapan saja di menu Pengaturan Tampilan.</p>
                <div class="grid grid-cols-1 gap-3">
                    @php
                        $presetOptions = [
                            ['value' => 'institution_organization', 'label' => 'Katering Instansi & Organisasi', 'desc' => 'Daftar pesanan, ringkasan anggaran, dan pemesanan cepat.', 'icon' => '🏢'],
                            ['value' => 'operations_logistics', 'label' => 'Operasional & Dapur', 'desc' => 'Pelacakan persiapan, status pengiriman, dan kurir.', 'icon' => '🚛'],
                            ['value' => 'finance_audit', 'label' => 'Keuangan & Pembayaran', 'desc' => 'Ringkasan tagihan, verifikasi bukti bayar, dan laporan.', 'icon' => '💼'],
                            ['value' => 'personal_customer', 'label' => 'Pelanggan Perorangan', 'desc' => 'Alur pemesanan sederhana dengan menu favorit dan pelacakan.', 'icon' => '👤'],
                            ['value' => 'minimal_focus', 'label' => 'Tampilan Ringkas Esensial', 'desc' => 'Antarmuka bersih bebas gangguan dengan fitur utama.', 'icon' => '🧘'],
                        ];
                    @endphp
                    @foreach($presetOptions as $opt)
                        <label class="flex items-center p-4 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer hover:border-emerald-500 transition-colors has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 has-[:checked]:ring-2 has-[:checked]:ring-emerald-500/20">
                            <input type="radio" name="preset" value="{{ $opt['value'] }}" class="text-emerald-600 focus:ring-emerald-500" {{ $opt['value'] === 'institution_organization' ? 'checked' : '' }}>
                            <div class="ml-3">
                                <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $opt['icon'] }} {{ $opt['label'] }}</span>
                                <p class="text-[11px] text-slate-500">{{ $opt['desc'] }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
                <div class="flex justify-between pt-6">
                    <button type="button" @click="step = 1" class="px-6 py-2.5 rounded-xl bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">← Kembali</button>
                    <button type="button" @click="step = 3" class="px-6 py-2.5 rounded-xl bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-500 transition-colors">Lanjut →</button>
                </div>
            </div>

            {{-- STEP 3: Density --}}
            <div x-show="step === 3" x-transition>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-1">Tingkat kepadatan tampilan</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-6">Seberapa padat informasi yang ingin ditampilkan pada antarmuka Anda?</p>
                <div class="space-y-3">
                    <label class="flex items-center p-4 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer hover:border-emerald-500 transition-colors has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 has-[:checked]:ring-2 has-[:checked]:ring-emerald-500/20">
                        <input type="radio" name="density" value="compact" class="text-emerald-600 focus:ring-emerald-500">
                        <div class="ml-3">
                            <span class="text-sm font-bold text-slate-900 dark:text-white">📐 Ringkas</span>
                            <p class="text-[11px] text-slate-500">Kepadatan tinggi, lebih banyak data terlihat, jarak antar elemen lebih kecil.</p>
                        </div>
                    </label>
                    <label class="flex items-center p-4 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer hover:border-emerald-500 transition-colors has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 has-[:checked]:ring-2 has-[:checked]:ring-emerald-500/20">
                        <input type="radio" name="density" value="comfortable" class="text-emerald-600 focus:ring-emerald-500" checked>
                        <div class="ml-3">
                            <span class="text-sm font-bold text-slate-900 dark:text-white">✨ Standar</span>
                            <p class="text-[11px] text-slate-500">Jarak elemen seimbang dengan hierarki visual yang jelas.</p>
                        </div>
                    </label>
                    <label class="flex items-center p-4 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer hover:border-emerald-500 transition-colors has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 has-[:checked]:ring-2 has-[:checked]:ring-emerald-500/20">
                        <input type="radio" name="density" value="spacious" class="text-emerald-600 focus:ring-emerald-500">
                        <div class="ml-3">
                            <span class="text-sm font-bold text-slate-900 dark:text-white">🌿 Luas</span>
                            <p class="text-[11px] text-slate-500">Tata letak lebih longgar, nyaman dibaca dan ramah aksesibilitas.</p>
                        </div>
                    </label>
                </div>
                <div class="flex justify-between pt-6">
                    <button type="button" @click="step = 2" class="px-6 py-2.5 rounded-xl bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">← Kembali</button>
                    <button type="submit" class="px-8 py-2.5 rounded-xl bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-500 transition-colors shadow-sm">Selesaikan Pengaturan ✓</button>
                </div>
            </div>
        </form>

        <p class="text-center text-[11px] text-slate-400 dark:text-slate-500 mt-6">
            <a href="{{ route('dashboard') }}" class="hover:underline">Lewati untuk saat ini →</a>
        </p>
    </div>
</body>
</html>
