<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50 dark:bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Daftar Akun — E-Catering</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center p-6 text-slate-800 dark:text-slate-100 font-sans antialiased bg-slate-50 dark:bg-slate-900">
    <div class="w-full max-w-md p-8 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
        <div class="text-center space-y-2">
            <div class="w-12 h-12 rounded-xl bg-emerald-600 flex items-center justify-center font-bold text-white shadow-md shadow-emerald-600/20 mx-auto text-xl">
                EC
            </div>
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Daftar Akun Baru</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Bergabung dengan E-Catering untuk memesan katering mudah</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf

            <!-- Name -->
            <div class="space-y-1">
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="Masukkan nama lengkap Anda"
                       class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 text-sm">
                @error('name') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Email -->
            <div class="space-y-1">
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Alamat Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="contoh: nama@email.com"
                       class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 text-sm">
                @error('email') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Password -->
            <div class="space-y-1">
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Kata Sandi</label>
                <input type="password" name="password" required placeholder="Minimal 8 karakter"
                       class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 text-sm">
                @error('password') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Password Confirmation -->
            <div class="space-y-1">
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Konfirmasi Kata Sandi</label>
                <input type="password" name="password_confirmation" required placeholder="Ulangi kata sandi"
                       class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 text-sm">
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full py-3 rounded-xl bg-emerald-600 text-white font-semibold text-sm hover:bg-emerald-500 transition-colors shadow-md shadow-emerald-600/20">
                Daftar Sekarang ➔
            </button>
        </form>

        <div class="text-center text-xs text-slate-500 dark:text-slate-400">
            Sudah memiliki akun? <a href="{{ route('login') }}" class="text-emerald-600 dark:text-emerald-400 font-semibold hover:underline">Masuk Sesi</a>
        </div>
    </div>
</body>
</html>
