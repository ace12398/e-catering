@props(['activities' => []])

<div class="p-6 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-elevation-1 space-y-4">
    <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Contextual Audit Trail Feed</h3>

    <div class="space-y-3">
        @forelse($activities as $act)
            <div class="flex items-center justify-between text-xs border-b border-slate-100 dark:border-slate-800/50 pb-2.5 last:border-0 last:pb-0">
                <div class="flex items-center space-x-2.5">
                    <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                    <div>
                        <p class="font-semibold text-slate-800 dark:text-slate-200 capitalize">{{ str_replace('_', ' ', $act['action'] ?? 'system_event') }}</p>
                        <p class="text-[10px] text-slate-400">Recorded Activity</p>
                    </div>
                </div>
                <span class="text-[10px] text-slate-400 font-mono">{{ \Carbon\Carbon::parse($act['created_at'] ?? now())->diffForHumans() }}</span>
            </div>
        @empty
            <div class="text-center py-4 text-xs text-slate-400">
                No recent activity records.
            </div>
        @endforelse
    </div>
</div>
