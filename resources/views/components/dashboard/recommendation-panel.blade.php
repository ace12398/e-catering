@props(['recommendations' => [], 'ctx' => []])

<div class="space-y-3">
    @foreach($recommendations as $rec)
        @php
            $styles = match($rec['severity'] ?? 'info') {
                'warning' => 'bg-amber-500/10 border-amber-500/30 text-amber-600 dark:text-amber-400',
                'success' => 'bg-emerald-500/10 border-emerald-500/30 text-emerald-600 dark:text-emerald-400',
                'danger' => 'bg-rose-500/10 border-rose-500/30 text-rose-600 dark:text-rose-400',
                default => 'bg-brand-500/10 border-brand-500/30 text-brand-600 dark:text-brand-400',
            };
            $icons = match($rec['severity'] ?? 'info') {
                'warning' => '⚠️',
                'success' => '🎉',
                'danger' => '🚨',
                default => '💡',
            };
        @endphp
        <div class="p-4 rounded-xl {{ $styles }} border flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <span class="text-xl">{{ $icons }}</span>
                <div>
                    <h4 class="text-xs font-bold">{{ $rec['title'] }}</h4>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ $rec['description'] }}</p>
                </div>
            </div>
            @if(!empty($rec['action_url']))
                <a href="{{ $rec['action_url'] }}" class="px-3 py-1.5 rounded-lg bg-white/80 dark:bg-slate-800 font-semibold text-xs hover:shadow-md transition-all border border-current/20">
                    {{ $rec['action_label'] ?? 'Lanjut →' }}
                </a>
            @endif
        </div>
    @endforeach

    {{-- Returning user welcome --}}
    @if(($ctx['is_returning'] ?? false) && empty($recommendations))
        <div class="p-4 rounded-xl bg-brand-500/10 border border-brand-500/30 flex items-center space-x-3">
            <span class="text-xl">👋</span>
            <div>
                <h4 class="text-xs font-bold text-brand-600 dark:text-brand-400">Selamat datang kembali, {{ $ctx['user_name'] ?? 'Pengguna' }}!</h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                    Anda telah melakukan {{ $ctx['order_count'] ?? 0 }} kali pemesanan dengan total transaksi {{ format_idr($ctx['total_spent'] ?? 0) }}.
                </p>
            </div>
        </div>
    @endif
</div>
