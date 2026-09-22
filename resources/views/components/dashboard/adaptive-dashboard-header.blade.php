@props(['user', 'ctx'])

<div class="p-6 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-elevation-1 flex flex-col md:flex-row md:items-center justify-between gap-6">
    <div class="flex items-center space-x-5">
        <div class="relative">
            <img src="{{ $ctx['avatar_url'] }}" 
                 alt="{{ $user->name }}" 
                 class="w-16 h-16 rounded-2xl object-cover border-2 border-brand-500 shadow-md">
            <span class="absolute -bottom-1 -right-1 w-3.5 h-3.5 rounded-full bg-emerald-500 border-2 border-white dark:border-slate-850" aria-label="Aktif"></span>
        </div>
        <div class="space-y-1">
            <div class="flex items-center space-x-2">
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $ctx['greeting'] }}, {{ $user->name }}!</h2>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-brand-500/10 text-brand-600 dark:text-brand-400">
                    {{ $ctx['role_label'] }}
                </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                @if(!empty($ctx['company_name']))
                    {{ $ctx['company_name'] }} •
                @endif
                Tampilan: <span class="font-bold text-slate-700 dark:text-slate-300 capitalize">{{ match($ctx['workspace_preset'] ?? 'personal') { 'institution_organization' => 'Instansi & Organisasi', 'operations_logistics' => 'Operasional & Dapur', 'finance_audit' => 'Keuangan & Pembayaran', default => 'Standar' } }}</span>
                • Kelengkapan Profil: <span class="font-bold {{ $ctx['profile_completion'] >= 80 ? 'text-emerald-500' : ($ctx['profile_completion'] >= 50 ? 'text-amber-500' : 'text-rose-500') }}">{{ $ctx['profile_completion'] }}%</span>
            </p>
        </div>
    </div>

    <div class="flex items-center space-x-3">
        @if($ctx['is_first_login'])
            <span class="px-3 py-1.5 rounded-xl bg-brand-500/10 border border-brand-500/30 text-[11px] font-bold text-brand-600 dark:text-brand-400">✨ Pengguna Baru</span>
        @elseif($ctx['order_count'] > 5)
            <span class="px-3 py-1.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-[11px] font-bold text-emerald-600 dark:text-emerald-400">⭐ Pelanggan Tetap</span>
        @endif
        <span class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-[11px] font-mono text-slate-500 dark:text-slate-400">
            ⏰ {{ now()->format('H:i T') }}
        </span>
    </div>
</div>
