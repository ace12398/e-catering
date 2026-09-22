<div class="relative group inline-block">
    <img src="{{ $profile?->getAvatarUrl() ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) }}" 
         alt="{{ $user->name }}" 
         class="w-20 h-20 rounded-full border-4 border-brand-500/20 object-cover shadow-lg">
    <div class="absolute inset-0 rounded-full bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
        <span class="text-[10px] font-bold text-white uppercase tracking-wider">Change</span>
    </div>
</div>
