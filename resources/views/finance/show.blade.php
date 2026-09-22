<x-dashboard-layout>
    <x-slot name="title">Detail Invoice {{ $invoice->invoice_number }} — E-Catering</x-slot>
    <x-slot name="toolbarTitle">Detail Invoice</x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold">❌ {{ session('error') }}</div>
        @endif

        <a href="{{ route('finance.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-emerald-600 transition-colors">
            ← Kembali ke Keuangan
        </a>

        @php
            $statusConfig = [
                'belum_dibayar'      => ['label'=>'Belum Dibayar','bg'=>'bg-amber-100','text'=>'text-amber-700'],
                'menunggu_verifikasi'=> ['label'=>'Menunggu Verifikasi','bg'=>'bg-blue-100','text'=>'text-blue-700'],
                'lunas'              => ['label'=>'Lunas','bg'=>'bg-emerald-100','text'=>'text-emerald-700'],
                'ditolak'            => ['label'=>'Ditolak','bg'=>'bg-rose-100','text'=>'text-rose-700'],
                'unpaid'             => ['label'=>'Belum Dibayar','bg'=>'bg-amber-100','text'=>'text-amber-700'],
                'paid'               => ['label'=>'Lunas','bg'=>'bg-emerald-100','text'=>'text-emerald-700'],
            ];
            $sc = $statusConfig[$invoice->status] ?? ['label'=>strtoupper($invoice->status),'bg'=>'bg-slate-100','text'=>'text-slate-700'];
            $canVerify = in_array($invoice->status, ['menunggu_verifikasi', 'unpaid']);
            $canReject = in_array($invoice->status, ['menunggu_verifikasi', 'unpaid']);
        @endphp

        {{-- Invoice Header --}}
        <div class="p-6 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex flex-col sm:flex-row justify-between gap-4 pb-5 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $invoice->invoice_number }}</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Dibuat: {{ $invoice->created_at->format('d F Y, H:i') }} WIB</p>
                    @if($invoice->due_date)
                    <p class="text-xs text-slate-500 mt-0.5">Jatuh Tempo: {{ $invoice->due_date->format('d F Y') }}</p>
                    @endif
                </div>
                <span class="px-4 py-2 rounded-xl text-sm font-bold {{ $sc['bg'] }} {{ $sc['text'] }}">
                    {{ $sc['label'] }}
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 py-5 border-b border-slate-100 dark:border-slate-800 text-xs">
                <div class="space-y-2">
                    <p class="font-bold text-slate-400 uppercase">Pelanggan</p>
                    <p class="font-semibold text-slate-900 dark:text-white">{{ $invoice->customer?->name ?? '-' }}</p>
                    <p class="text-slate-500">{{ $invoice->customer?->email ?? '-' }}</p>
                    <p class="text-slate-500">{{ $invoice->customer?->profile?->company_name ?? '-' }}</p>
                </div>
                <div class="space-y-2">
                    <p class="font-bold text-slate-400 uppercase">Detail Pesanan</p>
                    @if($invoice->order)
                    <a href="{{ route('orders.show', $invoice->order->id) }}" class="font-semibold text-emerald-600 hover:underline">{{ $invoice->order->order_number }}</a>
                    <p class="text-slate-500">{{ $invoice->order->items->count() }} item pesanan</p>
                    <p class="text-slate-500">Metode: {{ $invoice->order->payment_method ?? '-' }}</p>
                    @endif
                </div>
            </div>

            {{-- Amount Summary --}}
            <div class="py-5 space-y-2 border-b border-slate-100 dark:border-slate-800">
                <div class="flex justify-between text-xs text-slate-500"><span>Subtotal</span><span>{{ format_idr($invoice->subtotal) }}</span></div>
                <div class="flex justify-between text-xs text-slate-500"><span>Pajak (11%)</span><span>{{ format_idr($invoice->tax) }}</span></div>
                @if($invoice->discount > 0)
                <div class="flex justify-between text-xs text-rose-500"><span>Diskon</span><span>-{{ format_idr($invoice->discount) }}</span></div>
                @endif
                <div class="flex justify-between text-sm font-extrabold text-slate-900 dark:text-white pt-2 border-t border-slate-100 dark:border-slate-800">
                    <span>Total Tagihan</span>
                    <span class="text-emerald-600 dark:text-emerald-400">{{ format_idr($invoice->grand_total) }}</span>
                </div>
            </div>

            {{-- Bukti Pembayaran --}}
            @if($invoice->payment_proof)
            <div class="py-5 border-b border-slate-100 dark:border-slate-800">
                <p class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-3">Bukti Pembayaran</p>
                @php $ext = pathinfo($invoice->payment_proof, PATHINFO_EXTENSION); @endphp
                @if(in_array(strtolower($ext), ['jpg','jpeg','png','webp']))
                    <img src="{{ Storage::disk('public')->url($invoice->payment_proof) }}"
                         alt="Bukti Pembayaran"
                         class="max-w-sm w-full rounded-xl border border-slate-200 shadow-sm">
                @else
                    <a href="{{ Storage::disk('public')->url($invoice->payment_proof) }}"
                       target="_blank"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 transition-colors">
                        📄 Lihat Dokumen Bukti Pembayaran
                    </a>
                @endif
                @if($invoice->payment_note)
                <p class="text-xs text-slate-500 mt-2">Catatan: {{ $invoice->payment_note }}</p>
                @endif
            </div>
            @else
            <div class="py-5 border-b border-slate-100 dark:border-slate-800">
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-700">
                    ⚠️ Belum ada bukti pembayaran yang diunggah oleh pelanggan.
                </div>
            </div>
            @endif

            {{-- Verifikasi Info --}}
            @if($invoice->verified_at)
            <div class="py-4">
                <p class="text-xs text-slate-400">Diverifikasi oleh: <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $invoice->verifiedBy?->name ?? 'Admin' }}</span> pada {{ $invoice->verified_at->format('d F Y, H:i') }} WIB</p>
            </div>
            @endif
        </div>

        {{-- Action Buttons --}}
        @if($canVerify || $canReject)
        <div class="p-6 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Tindakan Verifikasi</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @if($canVerify)
                <form method="POST" action="{{ route('finance.verify', $invoice->id) }}" onsubmit="return confirm('Konfirmasi verifikasi pembayaran ini?')">
                    @csrf @method('PATCH')
                    <button type="submit" class="w-full py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-colors shadow-sm">
                        ✅ Verifikasi Pembayaran
                    </button>
                </form>
                @endif
                @if($canReject)
                <form method="POST" action="{{ route('finance.reject', $invoice->id) }}" onsubmit="return confirm('Tolak bukti pembayaran ini?')">
                    @csrf @method('PATCH')
                    <input type="hidden" name="reason" value="Bukti pembayaran tidak sesuai atau tidak valid.">
                    <button type="submit" class="w-full py-3 rounded-xl bg-rose-100 hover:bg-rose-200 text-rose-700 font-bold text-xs transition-colors border border-rose-200">
                        ✕ Tolak Pembayaran
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endif
    </div>
</x-dashboard-layout>
