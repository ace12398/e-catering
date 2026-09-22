<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50 dark:bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk Sesi — E-Catering</title>
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
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">E-Catering</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Masuk ke akun pengguna Anda</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <!-- Username / Email -->
            <div class="space-y-1">
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Username</label>
                <input type="text" name="login" value="{{ old('login', 'pelanggan') }}" required autofocus placeholder="Masukkan username (contoh: pelanggan)"
                       class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 text-sm">
                @error('login') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Password -->
            <div class="space-y-1">
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Kata Sandi</label>
                <input type="password" name="password" value="12345678" required placeholder="Masukkan kata sandi (12345678)"
                       class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 text-sm">
                @error('password') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 dark:border-slate-700 text-emerald-600 focus:ring-emerald-500">
                    <span class="text-slate-600 dark:text-slate-400">Ingat Saya</span>
                </label>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full py-3 rounded-xl bg-emerald-600 text-white font-semibold text-sm hover:bg-emerald-500 transition-colors shadow-md shadow-emerald-600/20">
                Masuk Sesi ➔
            </button>
        </form>

        <!-- Akun Demo -->
        <div class="p-3.5 rounded-xl bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-[11px] space-y-1 text-slate-600 dark:text-slate-400">
            <p class="font-bold text-slate-800 dark:text-slate-200">Akun Uji Coba (Kata Sandi: 12345678):</p>
            <div class="grid grid-cols-2 gap-2 pt-1">
                <div>Customer / Pelanggan: <code class="text-emerald-600 dark:text-emerald-400 font-mono font-bold">pelanggan</code></div>
                <div>Admin: <code class="text-emerald-600 dark:text-emerald-400 font-mono font-bold">admin</code></div>
            </div>
        </div>

        <div class="text-center text-xs text-slate-500 dark:text-slate-400">
            Belum memiliki akun? <a href="{{ route('register') }}" class="text-emerald-600 dark:text-emerald-400 font-semibold hover:underline">Daftar Akun Baru</a>
        </div>
    </div>
</body>
</html>
