@props(['items' => []])

<div class="overflow-x-auto">
    <table class="w-full text-xs text-left text-slate-500 dark:text-slate-400">
        <thead class="text-[10px] uppercase font-bold bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-300">
            <tr>
                <th class="px-4 py-2.5">Item Name</th>
                <th class="px-4 py-2.5 text-center">Qty</th>
                <th class="px-4 py-2.5 text-right">Price</th>
                <th class="px-4 py-2.5 text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            @forelse($items as $item)
                <tr>
                    <td class="px-4 py-2.5 font-medium text-slate-900 dark:text-white">{{ $item->menu->name ?? 'Catering Box Item' }}</td>
                    <td class="px-4 py-2.5 text-center font-mono">{{ $item->quantity }}</td>
                    <td class="px-4 py-2.5 text-right font-mono">{{ format_idr($item->price) }}</td>
                    <td class="px-4 py-2.5 text-right font-mono font-bold text-slate-900 dark:text-white">{{ format_idr($item->subtotal) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-4 text-center text-slate-400">Standard Catering Buffet Package</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
