@props(['user'])

<div class="profile-content-box p-6 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-elevation-1 space-y-4 overflow-hidden">
    <div class="flex items-center justify-between flex-between-header">
        <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider adaptive-label">Pengaturan Tampilan Beranda</h3>
        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 badge-adaptive">Metode UCD</span>
    </div>

    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-between">
        <div>
            <p class="text-xs font-bold text-slate-900 dark:text-white capitalize stat-value">Preset: {{ str_replace('_', ' ', $user->workspacePreference?->active_preset ?? 'standar') }}</p>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 subtext">Tata letak antarmuka yang disesuaikan dengan preferensi Anda.</p>
        </div>
        <a href="{{ route('workspace.customize') }}" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:underline btn-adaptive">Pengaturan ➔</a>
    </div>
</div>
