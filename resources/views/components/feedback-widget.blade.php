<div x-data="{ open: false, rating: 0, comment: '', submitted: false }" class="fixed bottom-6 right-6 z-50">
    {{-- Toggle Button --}}
    <button @click="open = !open; submitted = false" 
            x-show="!open"
            aria-label="Beri penilaian tampilan halaman ini"
            class="w-12 h-12 rounded-full bg-brand-600 text-white shadow-lg shadow-brand-600/30 hover:bg-brand-500 transition-all flex items-center justify-center text-lg hover:scale-110">
        💬
    </button>

    {{-- Feedback Panel --}}
    <div x-show="open" x-transition 
         class="w-80 rounded-2xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-elevation-2 p-5 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h4 class="text-sm font-bold text-slate-900 dark:text-white">Penilaian Tampilan</h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Metode UCD (ISO 9241-210) — Evaluasi Antarmuka</p>
            </div>
            <button @click="open = false" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
        </div>

        <template x-if="!submitted">
            <div class="space-y-4">
                <div>
                    <p class="text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Seberapa berguna tampilan halaman ini?</p>
                    <div class="flex space-x-1">
                        <template x-for="star in 5">
                            <button type="button" @click="rating = star" 
                                    :class="star <= rating ? 'text-amber-400' : 'text-slate-300 dark:text-slate-600'"
                                    class="text-2xl hover:scale-110 transition-transform cursor-pointer">★</button>
                        </template>
                    </div>
                </div>
                <div>
                    <textarea x-model="comment" rows="2" placeholder="Opsional: Tuliskan masukan atau saran Anda..." 
                              class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:border-brand-500"></textarea>
                </div>
                <button @click="
                    if (rating > 0) {
                        fetch('{{ route('feedback.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ page: window.location.pathname, rating: rating, comment: comment })
                        }).then(() => { submitted = true; });
                    }
                " class="w-full py-2.5 rounded-xl bg-brand-600 text-white font-bold text-xs hover:bg-brand-500 transition-colors" :disabled="rating === 0" :class="rating === 0 ? 'opacity-50 cursor-not-allowed' : ''">
                    Kirim Penilaian
                </button>
            </div>
        </template>

        <template x-if="submitted">
            <div class="text-center py-4 space-y-2">
                <div class="text-3xl">🙏</div>
                <p class="text-sm font-bold text-slate-900 dark:text-white">Terima Kasih!</p>
                <p class="text-[11px] text-slate-500">Masukan Anda sangat membantu dalam penyempurnaan antarmuka aplikasi.</p>
            </div>
        </template>
    </div>
</div>
