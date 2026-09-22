<x-dashboard-layout>
    <x-slot name="title">Detail Pesanan {{ $order->order_number }} — E-Catering</x-slot>
    <x-slot name="toolbarTitle">Detail Pesanan</x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2">
                ✅ {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 text-rose-800 text-xs font-bold flex items-center gap-2">
                ❌ {{ session('error') }}
            </div>
        @endif

        {{-- Back --}}
        <a href="{{ route('orders.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-emerald-600 transition-colors">
            ← Kembali ke Daftar Pesanan
        </a>

        @php
            $statusVal = is_object($order->status) ? $order->status->value : (string)$order->status;
            $statusMap = [
                'menunggu_pembayaran' => ['label'=>'Menunggu Pembayaran','color'=>'amber','bg'=>'bg-amber-100','text'=>'text-amber-700'],
                'menunggu_verifikasi' => ['label'=>'Menunggu Verifikasi','color'=>'blue','bg'=>'bg-blue-100','text'=>'text-blue-700'],
                'sedang_diproses'     => ['label'=>'Sedang Diproses','color'=>'indigo','bg'=>'bg-indigo-100','text'=>'text-indigo-700'],
                'sedang_dimasak'      => ['label'=>'Sedang Dimasak','color'=>'orange','bg'=>'bg-orange-100','text'=>'text-orange-700'],
                'sedang_dikirim'      => ['label'=>'Sedang Dikirim','color'=>'blue','bg'=>'bg-blue-100','text'=>'text-blue-700'],
                'selesai'             => ['label'=>'Selesai','color'=>'emerald','bg'=>'bg-emerald-100','text'=>'text-emerald-700'],
                'dibatalkan'          => ['label'=>'Dibatalkan','color'=>'rose','bg'=>'bg-rose-100','text'=>'text-rose-700'],
                // Fallback lama
                'pending'    => ['label'=>'Menunggu Pembayaran','color'=>'amber','bg'=>'bg-amber-100','text'=>'text-amber-700'],
                'preparing'  => ['label'=>'Sedang Diproses','color'=>'indigo','bg'=>'bg-indigo-100','text'=>'text-indigo-700'],
                'on_delivery'=> ['label'=>'Sedang Dikirim','color'=>'blue','bg'=>'bg-blue-100','text'=>'text-blue-700'],
                'completed'  => ['label'=>'Selesai','color'=>'emerald','bg'=>'bg-emerald-100','text'=>'text-emerald-700'],
                'cancelled'  => ['label'=>'Dibatalkan','color'=>'rose','bg'=>'bg-rose-100','text'=>'text-rose-700'],
            ];
            $statusInfo = $statusMap[$statusVal] ?? ['label'=>strtoupper($statusVal),'color'=>'slate','bg'=>'bg-slate-100','text'=>'text-slate-700'];
            $isOwner = auth()->id() === $order->user_id;
            $isAdmin = auth()->user()->isAdmin();
            $paymentMethod = strtolower($order->payment_method ?? '');
            $isQris = str_contains($paymentMethod, 'qris');
            $isTransfer = str_contains($paymentMethod, 'transfer');
            $isTunai = str_contains($paymentMethod, 'tunai') || str_contains($paymentMethod, 'cash');
            $canUpload = $isOwner && in_array($statusVal, ['menunggu_pembayaran', 'pending']);
            $canCancel = $isOwner && in_array($statusVal, ['menunggu_pembayaran', 'menunggu_verifikasi', 'pending']);
            
            // Timeline steps
            $timelineSteps = [
                ['key'=>'menunggu_pembayaran','label'=>'Menunggu Pembayaran','icon'=>'🕐'],
                ['key'=>'menunggu_verifikasi','label'=>'Bukti Dikirim','icon'=>'📋'],
                ['key'=>'sedang_diproses','label'=>'Diverifikasi','icon'=>'✔'],
                ['key'=>'sedang_dimasak','label'=>'Sedang Dimasak','icon'=>'🍳'],
                ['key'=>'sedang_dikirim','label'=>'Sedang Dikirim','icon'=>'🚚'],
                ['key'=>'selesai','label'=>'Selesai','icon'=>'✅'],
            ];
            $statusOrder = array_column($timelineSteps, 'key');
            $currentIdx = array_search($statusVal, $statusOrder);
            // Fallback untuk status lama
            if ($currentIdx === false) {
                $legacyMap = ['pending'=>0,'preparing'=>2,'on_delivery'=>4,'completed'=>5,'cancelled'=>-1];
                $currentIdx = $legacyMap[$statusVal] ?? 0;
            }
        @endphp

        {{-- Order Header --}}
        <div class="p-6 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 pb-5 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $order->order_number }}</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Pesanan dibuat: {{ $order->created_at->format('d F Y, H:i') }} WIB</p>
                    <p class="text-xs text-slate-500 mt-0.5">Metode: <span class="font-semibold">{{ $order->payment_method ?? '-' }}</span></p>
                </div>
                <span class="px-3 py-1.5 rounded-full text-xs font-bold uppercase border {{ $statusInfo['bg'] }} {{ $statusInfo['text'] }} border-current">
                    {{ $statusInfo['label'] }}
                </span>
            </div>

            {{-- Timeline --}}
            @if($statusVal !== 'dibatalkan' && $statusVal !== 'cancelled')
            <div class="py-5 border-b border-slate-100 dark:border-slate-800">
                <p class="text-[11px] font-bold text-slate-400 uppercase mb-3">Progres Pesanan</p>
                <div class="flex items-center w-full overflow-x-auto pb-2">
                    @foreach($timelineSteps as $i => $step)
                        @php
                            $isDone = $currentIdx !== false && $i <= $currentIdx;
                            $isActive = $i == $currentIdx;
                        @endphp
                        <div class="flex items-center {{ $i > 0 ? '' : '' }}">
                            @if($i > 0)
                                <div class="h-0.5 w-8 sm:w-12 {{ $isDone ? 'bg-emerald-400' : 'bg-slate-200 dark:bg-slate-700' }} shrink-0"></div>
                            @endif
                            <div class="flex flex-col items-center shrink-0">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm
                                    {{ $isDone ? 'bg-emerald-500 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-400' }}
                                    {{ $isActive ? 'ring-2 ring-emerald-400 ring-offset-2' : '' }}">
                                    {{ $isDone ? '✓' : $step['icon'] }}
                                </div>
                                <span class="text-[10px] font-semibold mt-1 text-center w-16
                                    {{ $isDone ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-400' }}">
                                    {{ $step['label'] }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Order Info --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 py-5 border-b border-slate-100 dark:border-slate-800 text-xs">
                <div>
                    <span class="block font-semibold text-slate-400 uppercase mb-1">Alamat Pengiriman</span>
                    <p class="text-slate-800 dark:text-slate-200 font-medium">{{ $order->delivery_address ?? 'Tidak ada alamat' }}</p>
                </div>
                <div>
                    <span class="block font-semibold text-slate-400 uppercase mb-1">Estimasi Pengiriman</span>
                    <p class="text-slate-800 dark:text-slate-200 font-medium">{{ $order->delivery_time?->format('d F Y, H:i') ?? '-' }} WIB</p>
                </div>
                @if($order->notes)
                <div class="sm:col-span-2">
                    <span class="block font-semibold text-slate-400 uppercase mb-1">Catatan Pesanan</span>
                    <p class="text-slate-800 dark:text-slate-200">{{ $order->notes }}</p>
                </div>
                @endif
            </div>

            {{-- Items --}}
            @if($order->items->isNotEmpty())
            <div class="py-4 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-3">Item Pesanan</h3>
                <div class="space-y-2">
                    @foreach($order->items as $item)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <span class="text-lg">🍱</span>
                            <div>
                                <span class="text-xs font-bold text-slate-900 dark:text-white">{{ $item->item_name }}</span>
                                <p class="text-[11px] text-slate-400">{{ format_idr($item->unit_price) }} × {{ $item->quantity }} porsi</p>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ format_idr($item->subtotal) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Price Summary --}}
            <div class="pt-4 space-y-2">
                <div class="flex justify-between text-xs text-slate-500">
                    <span>Subtotal</span>
                    <span>{{ format_idr($order->subtotal) }}</span>
                </div>
                <div class="flex justify-between text-xs text-slate-500">
                    <span>Pajak (PPN 11%)</span>
                    <span>{{ format_idr($order->tax) }}</span>
                </div>
                @if($order->discount > 0)
                <div class="flex justify-between text-xs text-rose-500">
                    <span>Diskon</span>
                    <span>-{{ format_idr($order->discount) }}</span>
                </div>
                @endif
                <div class="flex justify-between text-sm font-extrabold text-slate-900 dark:text-white pt-2 border-t border-slate-100 dark:border-slate-800">
                    <span>Total Pembayaran</span>
                    <span class="text-emerald-600 dark:text-emerald-400">{{ format_idr($order->grand_total) }}</span>
                </div>
            </div>
        </div>

        {{-- QRIS Dummy Panel --}}
        @if($canUpload && $isQris)
        <div class="p-6 rounded-2xl bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-950/30 dark:to-purple-950/30 border border-indigo-200 dark:border-indigo-800 shadow-sm">
            <h3 class="text-sm font-bold text-indigo-800 dark:text-indigo-300 mb-4">📱 Bayar dengan QRIS</h3>
            <div class="flex flex-col sm:flex-row items-center gap-6">
                {{-- QR Code Dummy --}}
                <div class="w-40 h-40 bg-white border-4 border-indigo-300 rounded-2xl flex items-center justify-center shadow-inner shrink-0">
                    <div class="grid grid-cols-3 gap-1 p-2">
                        @for($r = 0; $r < 9; $r++)
                            <div class="w-4 h-4 rounded-sm {{ in_array($r, [0,2,4,6,8]) ? 'bg-slate-900' : 'bg-slate-200' }}"></div>
                        @endfor
                    </div>
                </div>
                <div class="space-y-2 text-xs">
                    <div class="p-3 rounded-xl bg-white dark:bg-slate-800 border border-indigo-200 space-y-1">
                        <p class="font-semibold text-slate-700 dark:text-slate-300">Merchant: E-Catering Indonesia</p>
                        <p class="text-slate-500">Nominal: <span class="font-bold text-indigo-700">{{ format_idr($order->grand_total) }}</span></p>
                        <p class="text-slate-500">No. Referensi: <span class="font-mono font-bold">{{ $order->order_number }}</span></p>
                    </div>
                    <p class="text-slate-500 text-[11px]">1. Buka aplikasi m-banking atau dompet digital Anda<br>2. Scan QR Code di atas<br>3. Konfirmasi nominal dan bayar<br>4. Upload bukti pembayaran di bawah</p>
                </div>
            </div>
        </div>
        @endif

        {{-- Transfer Bank Dummy --}}
        @if($canUpload && $isTransfer)
        <div class="p-6 rounded-2xl bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-950/30 dark:to-teal-950/30 border border-emerald-200 dark:border-emerald-800 shadow-sm">
            <h3 class="text-sm font-bold text-emerald-800 dark:text-emerald-300 mb-4">🏦 Transfer Bank</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                <div class="p-3 rounded-xl bg-white dark:bg-slate-800 border border-emerald-200 space-y-1">
                    <p class="text-[11px] font-semibold text-emerald-700 uppercase">Bank BCA</p>
                    <p class="text-lg font-extrabold text-slate-900 dark:text-white font-mono">1234-5678-90</p>
                    <p class="text-slate-500">a.n. E-Catering Indonesia</p>
                </div>
                <div class="p-3 rounded-xl bg-white dark:bg-slate-800 border border-emerald-200 space-y-1">
                    <p class="text-[11px] font-semibold text-emerald-700 uppercase">Bank Mandiri</p>
                    <p class="text-lg font-extrabold text-slate-900 dark:text-white font-mono">1230-0456-7890</p>
                    <p class="text-slate-500">a.n. E-Catering Indonesia</p>
                </div>
                <div class="p-3 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 border border-emerald-300 space-y-1">
                    <p class="text-[11px] font-semibold text-emerald-700 uppercase">Nominal Transfer</p>
                    <p class="text-lg font-extrabold text-emerald-700 dark:text-emerald-400">{{ format_idr($order->grand_total) }}</p>
                    <p class="text-slate-500">Sesuaikan nominal persis</p>
                </div>
            </div>
        </div>
        @endif

        {{-- Upload Bukti Pembayaran --}}
        @if($canUpload)
        <div class="p-6 rounded-2xl bg-white dark:bg-slate-850 border border-amber-200 dark:border-amber-800 shadow-sm">
            <h3 class="text-sm font-bold text-amber-800 dark:text-amber-300 mb-1">📤 Upload Bukti Pembayaran</h3>
            <p class="text-xs text-slate-500 mb-4">Setelah melakukan pembayaran, upload bukti transfer/screenshot di sini.</p>

            <form method="POST" action="{{ route('orders.upload-proof', $order->id) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase">File Bukti Pembayaran</label>
                    <input type="file" name="payment_proof" accept="image/*,.pdf" required
                        class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white"
                        id="payment-proof-file">
                    <p class="text-[11px] text-slate-400">Format: JPG, PNG, PDF. Maks 5MB.</p>
                </div>
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase">Catatan (opsional)</label>
                    <input type="text" name="payment_note" placeholder="Contoh: Transfer BCA jam 10:30"
                        class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs">
                </div>
                <button type="submit" class="w-full py-3 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs transition-colors shadow-sm">
                    📤 Kirim Bukti Pembayaran
                </button>
            </form>
        </div>
        @endif

        {{-- Status menunggu verifikasi --}}
        @if(in_array($statusVal, ['menunggu_verifikasi']) && $isOwner)
        <div class="p-5 rounded-2xl bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 text-center">
            <div class="text-3xl mb-2">⏳</div>
            <p class="text-sm font-bold text-blue-700 dark:text-blue-300">Bukti pembayaran sedang diverifikasi</p>
            <p class="text-xs text-slate-500 mt-1">Admin akan memverifikasi bukti pembayaran Anda dalam 1×24 jam kerja.</p>
            @if($order->invoice?->payment_proof)
            <p class="text-[11px] text-emerald-600 mt-2 font-semibold">✅ Bukti pembayaran sudah diterima</p>
            @endif
        </div>
        @endif

        {{-- Action Buttons --}}
        <div class="flex flex-col sm:flex-row justify-between items-center gap-3">
            <a href="{{ route('orders.index') }}" class="text-xs font-semibold text-slate-500 hover:underline">
                ← Kembali ke Daftar Pesanan
            </a>
            <div class="flex gap-3">
                @if($canCancel)
                <form method="POST" action="{{ route('orders.cancel', $order->id) }}" onsubmit="return confirm('Yakin ingin membatalkan pesanan ini?')">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-100 text-rose-700 hover:bg-rose-200 transition-colors border border-rose-200">
                        ✕ Batalkan Pesanan
                    </button>
                </form>
                @endif
                @if(in_array($statusVal, ['selesai', 'completed']))
                <a href="{{ route('menus.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 text-white hover:bg-emerald-500 transition-colors">
                    + Pesan Lagi
                </a>
                @endif
            </div>
        </div>
    </div>
</x-dashboard-layout>
