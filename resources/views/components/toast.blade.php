<div x-data
     x-show="$store.notification.show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-4"
     class="fixed bottom-5 right-5 z-50 px-4 py-3 rounded-xl bg-slate-900 dark:bg-slate-800 text-white shadow-2xl border border-slate-700 flex items-center space-x-3 max-w-sm">
    <div class="w-2 h-2 rounded-full bg-brand-400 animate-pulse"></div>
    <div class="text-sm font-medium" x-text="$store.notification.message"></div>
</div>
