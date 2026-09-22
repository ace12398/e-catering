@props(['recommendations' => [], 'profileCompletion' => 25])

<div class="space-y-3">
    @if($profileCompletion < 50)
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 flex items-center justify-between text-slate-900 dark:text-white">
            <div class="flex items-center space-x-3">
                <span class="text-xl">🚨</span>
                <div>
                    <h4 class="text-xs font-bold text-rose-500">Kelengkapan Profil Belum Lengkap ({{ $profileCompletion }}%)</h4>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Lengkapi alamat pengiriman dan nomor kontak untuk mempermudah pesanan.</p>
                </div>
            </div>
            <a href="{{ route('profile.edit') }}" class="px-3 py-1.5 rounded-lg bg-rose-600 text-white font-semibold text-xs hover:bg-rose-500 transition-colors">
                Lengkapi Profil ➔
            </a>
        </div>
    @elseif($profileCompletion < 80)
        <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-between text-slate-900 dark:text-white">
            <div class="flex items-center space-x-3">
                <span class="text-xl">⚠️</span>
                <div>
                    <h4 class="text-xs font-bold text-amber-500">Saran Kelengkapan Profil ({{ $profileCompletion }}%)</h4>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Unggah foto profil/avatar untuk melengkapi akun katering Anda.</p>
                </div>
            </div>
            <a href="{{ route('profile.edit') }}" class="px-3 py-1.5 rounded-lg bg-amber-600 text-white font-semibold text-xs hover:bg-amber-500 transition-colors">
                Unggah Foto ➔
            </a>
        </div>
    @else
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-between text-slate-900 dark:text-white">
            <div class="flex items-center space-x-3">
                <span class="text-xl">🎉</span>
                <div>
                    <h4 class="text-xs font-bold text-emerald-500">Akun Terverifikasi Lengkap ({{ $profileCompletion }}%)</h4>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Profil Anda sudah lengkap dan siap untuk seluruh transaksi pemesanan katering.</p>
                </div>
            </div>
        </div>
    @endif
</div>
