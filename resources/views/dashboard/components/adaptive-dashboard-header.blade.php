@props(['user', 'profileCompletion' => 25])

<div class="p-6 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-elevation-1 flex flex-col md:flex-row md:items-center justify-between gap-6">
    <div class="flex items-center space-x-5">
        <div class="relative">
            <img src="{{ $user->profile?->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) }}" 
                 alt="{{ $user->name }}" 
                 class="w-16 h-16 rounded-2xl object-cover border-2 border-brand-500 shadow-md">
            <span class="absolute -bottom-1 -right-1 w-3.5 h-3.5 rounded-full bg-emerald-500 border-2 border-white dark:border-slate-850"></span>
        </div>
        <div class="space-y-1">
            <div class="flex items-center space-x-2">
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Selamat Datang, {{ $user->name }}!</h2>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-brand-500/10 text-brand-600 dark:text-brand-400">
                    {{ is_object($user->role) && method_exists($user->role, 'label') ? $user->role->label() : ($user->role ?? 'Customer') }}
                </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Preset Tampilan: <span class="font-bold text-slate-700 dark:text-slate-300 capitalize">{{ match($user->workspacePreference?->active_preset ?? 'personal') { 'institution_organization' => 'Instansi & Organisasi', 'operations_logistics' => 'Operasional & Dapur', 'finance_audit' => 'Keuangan & Pembayaran', default => 'Standar' } }}</span>
                • Kelengkapan Profil: <span class="font-bold text-brand-600 dark:text-brand-400">{{ $profileCompletion }}%</span>
            </p>
        </div>
    </div>

    <!-- Adaptation Action Badge -->
    <div class="flex items-center space-x-3">
        <span class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-[11px] font-mono text-slate-500 dark:text-slate-400">
            ⏰ {{ now()->format('H:i T') }}
        </span>
    </div>
</div>
