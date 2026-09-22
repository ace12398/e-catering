<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>E-Catering — Pemesanan Katering Online</title>
    <meta name="description" content="Sistem pemesanan katering online menggunakan metode User Centered Design (ISO 9241-210).">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex flex-col justify-between text-slate-800 font-sans antialiased bg-slate-50">

    <!-- 1. NAVBAR -->
    <header class="border-b border-slate-200 bg-white/90 backdrop-blur sticky top-0 z-50 px-6 py-4">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-600 flex items-center justify-center font-bold text-white shadow-sm text-base">
                    EC
                </div>
                <span class="text-xl font-bold tracking-tight text-slate-900">E-Catering</span>
            </div>
            <div class="flex items-center space-x-3">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-4 py-2 text-xs font-bold rounded-xl bg-emerald-600 text-white hover:bg-emerald-500 transition-colors shadow-sm">
                            Beranda Utama ➔
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 text-xs font-semibold text-slate-700 hover:text-emerald-600 transition-colors">
                            Masuk
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-4 py-2 text-xs font-bold rounded-xl bg-emerald-600 text-white hover:bg-emerald-500 transition-colors shadow-sm">
                                Daftar Akun
                            </a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="flex-1">
        <!-- 2. HERO SECTION -->
        <section class="max-w-6xl mx-auto px-6 py-16 md:py-24 grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            <div class="space-y-6 text-left">
                <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold">
                    <span>🍱 Pemesanan Katering Adaptif (ISO 9241-210)</span>
                </div>

                <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-slate-900 leading-tight">
                    Selamat Datang di <span class="text-emerald-600">E-Catering</span>
                </h1>

                <p class="text-sm md:text-base text-slate-600 leading-relaxed">
                    Sistem pemesanan katering berbasis web yang dirancang dengan metode User Centered Design (ISO 9241-210). Antarmuka adaptif yang menyesuaikan peran pengguna, waktu akses, serta preferensi kerapatan tampilan.
                </p>

                <div class="flex flex-wrap items-center gap-3 pt-2">
                    <a href="{{ route('login') }}" class="px-6 py-3 rounded-xl bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-500 transition-all shadow-md shadow-emerald-600/20">
                        Pesan Sekarang ➔
                    </a>
                    <a href="#menu-unggulan" class="px-6 py-3 rounded-xl bg-white border border-slate-200 text-slate-700 font-semibold text-xs hover:bg-slate-100 transition-colors">
                        Lihat Menu
                    </a>
                </div>
            </div>

            <!-- HERO VISUAL -->
            <div class="relative">
                <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-4">
                    <div class="h-56 rounded-xl bg-emerald-50 flex items-center justify-center text-6xl overflow-hidden relative border border-emerald-100">
                        🍱
                        <div class="absolute top-3 right-3 px-3 py-1 rounded-full bg-emerald-600 text-white text-[10px] font-bold">
                            Favorit Pelanggan
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Paket Nasi Ayam Lengkuas</h3>
                            <p class="text-xs text-slate-500">Ayam goreng lengkuas, tahu, tempe, sambal</p>
                        </div>
                        <span class="text-base font-extrabold text-emerald-600">Rp 35.000</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- 3. TENTANG KAMI -->
        <section id="tentang" class="bg-white border-y border-slate-200 py-16 px-6">
            <div class="max-w-6xl mx-auto space-y-8 text-center max-w-3xl">
                <h2 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Tentang Platform E-Catering</h2>
                <p class="text-sm text-slate-600 leading-relaxed">
                    E-Catering adalah aplikasi pemesanan katering online yang dikembangkan khusus untuk memenuhi kebutuhan katering harian perorangan maupun korporat. Mengusung prinsip ISO 9241-210, aplikasi ini mengutamakan kenyamanan pengguna melalui antarmuka adaptif, alur transaksi transparan, dan integrasi operasional dapur serta armada pengiriman kurir.
                </p>
            </div>
        </section>

        <!-- 4. KEUNGGULAN -->
        <section id="mengapa-kami" class="py-16 px-6 max-w-6xl mx-auto space-y-12">
            <div class="text-center space-y-2 max-w-2xl mx-auto">
                <h2 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Keunggulan Layanan</h2>
                <p class="text-xs text-slate-500">Layanan katering terpercaya dengan standar mutu dan kepuasan pengguna terbaik.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-xl">
                        🍲
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">35+ Pilihan Menu</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">Varian masakan tradisional, bento box, snack box, dan paket minuman higienis.</p>
                </div>

                <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-xl">
                        🛒
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Pemesanan Mudah</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">Alur pemesanan yang cepat, praktis, dan intuitif melalui sistem online.</p>
                </div>

                <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-xl">
                        🛵
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Pengiriman Tepat Waktu</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">Jaminan ketepatan waktu pengiriman makanan dalam kondisi segar & hangat.</p>
                </div>

                <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-xl">
                        💳
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Pembayaran Transparan</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">Rincian invoice resmi dengan biaya pengiriman dan pajak PPN transparan.</p>
                </div>
            </div>
        </section>

        <!-- 5. CARA PEMESANAN -->
        <section class="bg-white border-y border-slate-200 py-16 px-6">
            <div class="max-w-6xl mx-auto space-y-12">
                <div class="text-center space-y-2 max-w-2xl mx-auto">
                    <h2 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Cara Pemesanan</h2>
                    <p class="text-xs text-slate-500">Tiga langkah mudah untuk memesan hidangan katering favorit Anda.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 text-center space-y-3">
                        <div class="w-12 h-12 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold mx-auto text-lg">1</div>
                        <h3 class="text-sm font-bold text-slate-900">Pilih Menu Hidangan</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">Pilih berbagai varian menu bento, nasi kotak, snack box, atau minuman sesuai kebutuhan acara Anda.</p>
                    </div>
                    <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 text-center space-y-3">
                        <div class="w-12 h-12 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold mx-auto text-lg">2</div>
                        <h3 class="text-sm font-bold text-slate-900">Konfirmasi & Pembayaran</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">Isi alamat pengiriman, tentukan catatan porsi, dan lakukan pembayaran dengan mudah.</p>
                    </div>
                    <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 text-center space-y-3">
                        <div class="w-12 h-12 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold mx-auto text-lg">3</div>
                        <h3 class="text-sm font-bold text-slate-900">Hidangan Tiba Diantar</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">Kurir kami mengantarkan hidangan hangat dan higienis tepat waktu ke lokasi Anda.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 6. MENU FAVORIT -->
        <section id="menu-unggulan" class="py-16 px-6 max-w-6xl mx-auto space-y-12">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Menu Favorit Pelanggan</h2>
                    <p class="text-xs text-slate-500">Pilihan paket hidangan yang paling sering dipesan pengguna.</p>
                </div>
                <a href="{{ route('login') }}" class="px-5 py-2.5 rounded-xl bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-500 transition-colors shadow-sm">
                    Lihat Seluruh 35+ Menu ➔
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-3">
                    <div class="h-40 rounded-xl bg-emerald-50 flex items-center justify-center text-4xl">🍱</div>
                    <h3 class="text-sm font-bold text-slate-900">Paket Nasi Ayam Lengkuas</h3>
                    <p class="text-xs text-slate-500">Ayam goreng lengkuas, tahu, tempe, lalapan segar, sambal.</p>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-sm font-extrabold text-emerald-600">Rp 35.000</span>
                        <a href="{{ route('login') }}" class="text-xs font-bold text-emerald-600 hover:underline">Pesan ➔</a>
                    </div>
                </div>
                <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-3">
                    <div class="h-40 rounded-xl bg-emerald-50 flex items-center justify-center text-4xl">🍲</div>
                    <h3 class="text-sm font-bold text-slate-900">Paket Nasi Rendang Daging</h3>
                    <p class="text-xs text-slate-500">Rendang sapi empuk, daun singkong rebus, sambal ijo.</p>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-sm font-extrabold text-emerald-600">Rp 45.000</span>
                        <a href="{{ route('login') }}" class="text-xs font-bold text-emerald-600 hover:underline">Pesan ➔</a>
                    </div>
                </div>
                <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-3">
                    <div class="h-40 rounded-xl bg-emerald-50 flex items-center justify-center text-4xl">🥗</div>
                    <h3 class="text-sm font-bold text-slate-900">Paket Vegetarian Sehat</h3>
                    <p class="text-xs text-slate-500">Tumis sayuran segar, tahu bacem, urap, buah segar.</p>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-sm font-extrabold text-emerald-600">Rp 38.000</span>
                        <a href="{{ route('login') }}" class="text-xs font-bold text-emerald-600 hover:underline">Pesan ➔</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- 7. TESTIMONI -->
        <section class="bg-white border-y border-slate-200 py-16 px-6">
            <div class="max-w-6xl mx-auto space-y-12">
                <div class="text-center space-y-2 max-w-2xl mx-auto">
                    <h2 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Testimoni Pelanggan</h2>
                    <p class="text-xs text-slate-500">Ulasan nyata dari mitra korporat dan pengguna E-Catering.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                        <div class="flex items-center space-x-1 text-amber-500 text-xs">⭐⭐⭐⭐⭐</div>
                        <p class="text-xs text-slate-600 italic">"Pemesanan katering harian kantor kami jadi jauh lebih praktis. Tampilannya sangat bersih dan mudah dipahami tim kami."</p>
                        <div class="pt-2 border-t border-slate-200">
                            <span class="text-xs font-bold text-slate-900 block">Siti Rahma</span>
                            <span class="text-[11px] text-slate-400">PT Rahma Jaya Abadi</span>
                        </div>
                    </div>
                    <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                        <div class="flex items-center space-x-1 text-amber-500 text-xs">⭐⭐⭐⭐⭐</div>
                        <p class="text-xs text-slate-600 italic">"Menu nasi rendang dan bento box-nya selalu tepat waktu diantar ke kantor. Porsi dan rasanya sangat memuaskan."</p>
                        <div class="pt-2 border-t border-slate-200">
                            <span class="text-xs font-bold text-slate-900 block">Dewi Anggraeni</span>
                            <span class="text-[11px] text-slate-400">PT Maju Bersama</span>
                        </div>
                    </div>
                    <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                        <div class="flex items-center space-x-1 text-amber-500 text-xs">⭐⭐⭐⭐⭐</div>
                        <p class="text-xs text-slate-600 italic">"Sistem invoice otomatis dan pelacakan pengiriman kurirnya sangat membantu bagian keuangan perusahaan kami."</p>
                        <div class="pt-2 border-t border-slate-200">
                            <span class="text-xs font-bold text-slate-900 block">Andi Kusuma</span>
                            <span class="text-[11px] text-slate-400">CV Karya Mandiri</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 8. CALL TO ACTION (CTA) -->
        <section class="py-16 px-6 max-w-6xl mx-auto">
            <div class="p-8 md:p-12 rounded-3xl bg-emerald-600 text-white text-center space-y-6 shadow-xl shadow-emerald-600/20">
                <h2 class="text-2xl md:text-4xl font-extrabold tracking-tight">Siap Memesan Katering Anda Hari Ini?</h2>
                <p class="text-xs md:text-sm text-emerald-100 max-w-2xl mx-auto leading-relaxed">
                    Dapatkan akses ke 35+ varian paket hidangan katering dengan antarmuka adaptif yang dirancang sesuai kebutuhan Anda.
                </p>
                <div class="pt-2">
                    <a href="{{ route('login') }}" class="inline-block px-8 py-3.5 rounded-xl bg-white text-emerald-700 font-bold text-xs hover:bg-emerald-50 transition-all shadow-md">
                        Masuk & Mulai Pesan ➔
                    </a>
                </div>
            </div>
        </section>
    </main>

    <!-- 9. FOOTER -->
    <footer class="border-t border-slate-200 py-6 px-6 bg-white text-center text-xs text-slate-500">
        <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3">
            <span>&copy; {{ date('Y') }} E-Catering. Seluruh hak cipta dilindungi.</span>
            <span>Perancangan Antarmuka Berdasarkan Metode User Centered Design (ISO 9241-210).</span>
        </div>
    </footer>
</body>
</html>
