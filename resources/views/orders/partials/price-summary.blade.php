@props(['total' => 0])

@php
    $subtotal = $total * 0.89;
    $tax = $total * 0.11;
@endphp

<div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-2 text-xs">
    <div class="flex justify-between text-slate-500 dark:text-slate-400">
        <span>Subtotal</span>
        <span>{{ format_idr($subtotal) }}</span>
    </div>
    <div class="flex justify-between text-slate-500 dark:text-slate-400">
        <span>Tax (PPN 11%)</span>
        <span>{{ format_idr($tax) }}</span>
    </div>
    <div class="border-t border-slate-200 dark:border-slate-800 pt-2 flex justify-between font-bold text-sm text-slate-900 dark:text-white">
        <span>Grand Total</span>
        <span class="text-brand-600 dark:text-brand-400">{{ format_idr($total) }}</span>
    </div>
</div>
