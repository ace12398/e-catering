@props(['user'])

@php
    $profile = $user->profile;
    $completion = $profile?->calculateCompletion() ?? 40;
@endphp

<div class="profile-content-box p-6 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4 overflow-hidden">
    <div class="flex items-center justify-between flex-between-header">
        <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider adaptive-label">Kelengkapan Profil</h3>
        <span class="text-xs font-extrabold stat-value {{ $completion >= 100 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
            {{ $completion }}% Terisi
        </span>
    </div>

    <!-- Progress Bar -->
    <div class="w-full h-2.5 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden progress-bar-adaptive">
        <div class="h-full {{ $completion >= 100 ? 'bg-emerald-600' : 'bg-amber-500' }} rounded-full transition-all duration-700" style="width: {{ max($completion, 10) }}%"></div>
    </div>

    <!-- 5 Checklist Items (20% each) -->
    <div class="space-y-2 pt-1 text-xs subtext">
        <div class="flex items-center justify-between {{ !empty($user->name) ? 'text-emerald-600 font-semibold' : 'text-slate-400' }}">
            <span class="flex items-center space-x-2">
                <span>{{ !empty($user->name) ? '✓' : '○' }}</span>
                <span>Nama Lengkap</span>
            </span>
            <span class="text-[10px] font-bold">20%</span>
        </div>
        <div class="flex items-center justify-between {{ !empty($user->username) ? 'text-emerald-600 font-semibold' : 'text-slate-400' }}">
            <span class="flex items-center space-x-2">
                <span>{{ !empty($user->username) ? '✓' : '○' }}</span>
                <span>Username</span>
            </span>
            <span class="text-[10px] font-bold">20%</span>
        </div>
        <div class="flex items-center justify-between {{ !empty($profile?->phone_number) ? 'text-emerald-600 font-semibold' : 'text-slate-400' }}">
            <span class="flex items-center space-x-2">
                <span>{{ !empty($profile?->phone_number) ? '✓' : '○' }}</span>
                <span>Nomor HP</span>
            </span>
            <span class="text-[10px] font-bold">20%</span>
        </div>
        <div class="flex items-center justify-between {{ !empty($profile?->address) ? 'text-emerald-600 font-semibold' : 'text-slate-400' }}">
            <span class="flex items-center space-x-2">
                <span>{{ !empty($profile?->address) ? '✓' : '○' }}</span>
                <span>Alamat Pengiriman</span>
            </span>
            <span class="text-[10px] font-bold">20%</span>
        </div>
        <div class="flex items-center justify-between {{ !empty($profile?->avatar) ? 'text-emerald-600 font-semibold' : 'text-slate-400' }}">
            <span class="flex items-center space-x-2">
                <span>{{ !empty($profile?->avatar) ? '✓' : '○' }}</span>
                <span>Foto Profil</span>
            </span>
            <span class="text-[10px] font-bold">20%</span>
        </div>
    </div>
</div>
