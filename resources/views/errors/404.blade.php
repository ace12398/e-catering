<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50 dark:bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 Halaman Tidak Ditemukan — E-Catering</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center p-6 text-slate-800 dark:text-slate-100 font-sans antialiased bg-slate-50 dark:bg-slate-900">
    <div class="w-full max-w-md p-8 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm text-center space-y-6">
        <div class="w-16 h-16 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center font-bold text-amber-600 mx-auto text-2xl">
            404
        </div>
        <div class="space-y-2">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Halaman Tidak Ditemukan</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Maaf, halaman yang Anda cari tidak tersedia atau telah dipindahkan.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="inline-block px-6 py-3 rounded-xl bg-emerald-600 text-white font-semibold text-xs hover:bg-emerald-500 transition-colors shadow-sm">
            Kembali ke Beranda Utama ➔
        </a>
    </div>
</body>
</html>
