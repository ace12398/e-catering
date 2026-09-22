@props(['user'])

<div class="p-6 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-elevation-1 flex flex-col md:flex-row md:items-center justify-between gap-6">
    <div class="flex items-center space-x-5">
        <div class="relative">
            <img src="{{ $user->profile?->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) }}" 
                 alt="{{ $user->name }}" 
                 class="w-20 h-20 rounded-2xl object-cover border-2 border-brand-500 shadow-md">
            <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-emerald-500 border-2 border-white dark:border-slate-850" title="Active Account"></span>
        </div>
        <div class="space-y-1">
            <div class="flex items-center space-x-2">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">{{ $user->name }}</h2>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-brand-500/10 text-brand-600 dark:text-brand-400 border border-brand-500/20">
                    {{ $user->role->label() ?? 'User' }}
                </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">{{ $user->email }}</p>
            <div class="flex items-center space-x-3 text-[11px] text-slate-400">
                <span>Bergabung {{ $user->created_at->format('M Y') }}</span>
                <span>•</span>
                <span>Preset Tampilan: {{ match($user->workspacePreference?->active_preset ?? 'personal') { 'institution_organization' => 'Instansi & Organisasi', 'operations_logistics' => 'Operasional & Dapur', 'finance_audit' => 'Keuangan & Pembayaran', default => 'Standar' } }}</span>
            </div>
        </div>
    </div>

    <!-- Quick Role Adaptation Action -->
    <div class="flex items-center space-x-3">
        @if($user->isAdmin())
            <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-xl bg-purple-600/10 text-purple-600 dark:text-purple-400 border border-purple-500/20 font-semibold text-xs hover:bg-purple-600/20 transition-colors">
                ⚡ Panel Admin
            </a>
        @else
            <a href="{{ route('menus.index') }}" class="px-4 py-2 rounded-xl bg-brand-600 text-white font-semibold text-xs hover:bg-brand-500 transition-colors shadow-lg shadow-brand-600/20">
                🛒 Pesan Katering
            </a>
        @endif
    </div>
</div>
