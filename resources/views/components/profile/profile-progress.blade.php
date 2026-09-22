@props(['percentage' => 0])

<div class="space-y-1.5 w-full">
    <div class="flex justify-between items-center text-xs font-semibold">
        <span class="text-slate-500 dark:text-slate-400">Profile Strength</span>
        <span class="text-brand-600 dark:text-brand-400 font-bold">{{ $percentage }}%</span>
    </div>
    <div class="w-full h-2 rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
        <div class="h-full bg-gradient-to-r from-brand-600 to-brand-400 rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
    </div>
</div>
