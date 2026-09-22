<x-dashboard-layout>
    <x-slot name="title">Customer Dashboard</x-slot>
    <x-slot name="toolbarTitle">SCR-CUS-001 / Customer Dashboard Workspace</x-slot>

    <!-- EDIT MODE TOOLBAR BANNER -->
    <div x-data x-show="$store.workspace.editMode" 
         x-transition
         class="mb-6 p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-600 dark:text-amber-400 flex items-center justify-between">
        <div class="flex items-center space-x-2 text-sm font-semibold">
            <span>✏️ EDIT MODE ACTIVE:</span>
            <span class="font-normal">Drag widgets by their grip handles to personalize your layout.</span>
        </div>
        <div class="flex items-center space-x-2">
            <button @click="$store.workspace.saveLayout([])" class="px-3 py-1.5 rounded-lg bg-amber-600 text-white text-xs font-bold hover:bg-amber-500 transition-colors">
                Save Layout 💾
            </button>
        </div>
    </div>

    <!-- KPI STAT CARDS (4 COLUMNS) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- KPI 1 -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-elevation-1 space-y-2 relative">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">MONTHLY SPEND</span>
                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">+18.4%</span>
            </div>
            <div class="text-2xl font-extrabold text-slate-900 dark:text-white">Rp 1.450.000</div>
            <div class="text-xs text-slate-400">Target budget: Rp 2.000.000</div>
        </div>

        <!-- KPI 2 -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-elevation-1 space-y-2 relative">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">ACTIVE ORDERS</span>
                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-brand-500/10 text-brand-600 dark:text-brand-400">1 In-Prep</span>
            </div>
            <div class="text-2xl font-extrabold text-slate-900 dark:text-white">24 Orders</div>
            <div class="text-xs text-slate-400">Est. 12:15 PM Arrival</div>
        </div>

        <!-- KPI 3 -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-elevation-1 space-y-2 relative">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">FULFILLMENT RATE</span>
                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">98% On-Time</span>
            </div>
            <div class="text-2xl font-extrabold text-slate-900 dark:text-white">100% Score</div>
            <div class="text-xs text-slate-400">Zero missed deliveries</div>
        </div>

        <!-- KPI 4 -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-elevation-1 space-y-2 relative">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">TOTAL EFISIENSI BIAYA</span>
                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-brand-500/10 text-brand-600 dark:text-brand-400">20% Tier</span>
            </div>
            <div class="text-2xl font-extrabold text-slate-900 dark:text-white">Rp 290.000</div>
            <div class="text-xs text-slate-400">Penghematan bulan ini</div>
        </div>
    </div>

    <!-- MAIN DASHBOARD CONTENT GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- RECENT ORDERS DATA TABLE (2 COLS) -->
        <div class="lg:col-span-2 p-6 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-elevation-1 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center space-x-2">
                    <span>📜 Recent Catering Orders</span>
                </h3>
                <a href="#" class="text-xs font-semibold text-brand-600 dark:text-brand-400 hover:underline">View All ➔</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-400">
                    <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-700 dark:text-slate-300 font-bold uppercase border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="py-3 px-4">Order ID</th>
                            <th class="py-3 px-4">Vendor</th>
                            <th class="py-3 px-4">Total</th>
                            <th class="py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        <tr>
                            <td class="py-3 px-4 font-bold text-slate-900 dark:text-white">#ORD-10492</td>
                            <td class="py-3 px-4">Dapur Utama Catering</td>
                            <td class="py-3 px-4 font-semibold text-brand-600 dark:text-brand-400">Rp 101.450</td>
                            <td class="py-3 px-4"><span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-brand-500/10 text-brand-600 dark:text-brand-400">ON DELIVERY</span></td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4 font-bold text-slate-900 dark:text-white">#ORD-10491</td>
                            <td class="py-3 px-4">Warung Liwet Pasundan</td>
                            <td class="py-3 px-4 font-semibold text-brand-600 dark:text-brand-400">Rp 350.000</td>
                            <td class="py-3 px-4"><span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">COMPLETED</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- QUICK REORDER CARD (1 COL) -->
        <div class="p-6 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-elevation-1 space-y-4">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">⚡ Quick Reorder Favorite</h3>
            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-2">
                <div class="font-bold text-sm text-slate-900 dark:text-white">Chicken Teriyaki Bento Box</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">Dapur Utama Catering • Rp 45.000</div>
                <button @click="$store.notification.notify('Added 1x Chicken Teriyaki Bento to Cart!')" class="w-full py-2 rounded-lg bg-brand-600 text-white font-semibold text-xs hover:bg-brand-500 transition-colors">
                    + Quick Add to Cart
                </button>
            </div>
        </div>
    </div>
</x-dashboard-layout>
