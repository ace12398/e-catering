@props(['user'])

@php
    $logs = \App\Models\ActivityLog::where('user_id', $user->id)->latest()->take(4)->get();
@endphp

<div class="profile-content-box p-6 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-elevation-1 space-y-4 overflow-hidden">
    <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider adaptive-label">Riwayat Aktivitas Akun</h3>

    <div class="space-y-3">
        @forelse($logs as $log)
            <div class="flex items-center justify-between text-xs border-b border-slate-100 dark:border-slate-800/50 pb-2.5 last:border-0 last:pb-0 flex-between-header">
                <div>
                    <p class="font-semibold text-slate-800 dark:text-slate-200 capitalize stat-value">{{ str_replace('_', ' ', $log->action) }}</p>
                    <p class="text-[10px] text-slate-400 subtext">IP: {{ $log->ip_address ?? '127.0.0.1' }}</p>
                </div>
                <span class="text-[10px] text-slate-400 font-mono subtext">{{ $log->created_at->diffForHumans() }}</span>
            </div>
        @empty
            <div class="text-center py-4 text-xs text-slate-400">
                Belum ada riwayat aktivitas.
            </div>
        @endforelse
    </div>
</div>
