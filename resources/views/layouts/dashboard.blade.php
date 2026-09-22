<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-900" x-data :class="$store.theme.current === 'dark' ? 'dark' : ''">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} — E-Catering</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* TRUE ADAPTIVE UI CONTENT ENGINE CSS RULES */

        /* 1. SHAPE: CIRCLE (Centered Vertical Stack with Radial Inset Safety) */
        .shape-circle-box {
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            justify-content: center !important;
            text-align: center !important;
            box-sizing: border-box !important;
            /* NOTE: width/height are set by JS using explicit px values, NOT 100% */
        }
        .shape-circle-box > * {
            text-align: center !important;
            margin-left: auto !important;
            margin-right: auto !important;
        }
        .shape-circle-box .flex-between-header,
        .shape-circle-box .flex-header {
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 0.25rem !important;
            width: 100% !important;
        }

        /* 2. SHAPE: HEXAGON (Polygon Safe Inset Area) */
        .shape-hexagon-box {
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            justify-content: center !important;
            text-align: center !important;
            box-sizing: border-box !important;
        }
        .shape-hexagon-box .flex-between-header {
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            gap: 0.25rem !important;
        }

        /* 3. SHAPE: PILL (Linear Side Padding) */
        .shape-pill-box {
            padding-left: 1.5rem !important;
            padding-right: 1.5rem !important;
        }

        /* 4. ADAPTIVE IMAGE — scales proportionally inside adaptive components */
        .adaptive-img {
            object-fit: cover !important;
            max-width: 100% !important;
            height: auto !important;
            display: block !important;
        }
        .size-ultracompact .adaptive-img {
            width: 32px !important;
            height: 32px !important;
            border-radius: 6px !important;
        }
        .size-compact .adaptive-img {
            width: 48px !important;
            height: 48px !important;
            border-radius: 8px !important;
        }
        .size-spacious .adaptive-img {
            width: 64px !important;
            height: 64px !important;
        }
        /* Menu card images */
        .size-ultracompact .menu-img-adaptive {
            height: 60px !important;
            width: 100% !important;
            object-fit: cover !important;
        }
        .size-compact .menu-img-adaptive {
            height: 100px !important;
            width: 100% !important;
            object-fit: cover !important;
        }
        .size-spacious .menu-img-adaptive {
            height: 160px !important;
            width: 100% !important;
            object-fit: cover !important;
        }

        /* 5. SIZE THRESHOLDS & COMPONENT ADAPTATIONS */
        /* ULTRA COMPACT (<160px min-dim) */
        .size-ultracompact .hide-on-compact,
        .size-ultracompact .subtext,
        .size-ultracompact .secondary-info,
        .size-ultracompact .desc-text {
            display: none !important;
        }
        .size-ultracompact .stat-value {
            font-size: 0.95rem !important;
            line-height: 1.25rem !important;
            font-weight: 800 !important;
        }
        .size-ultracompact .adaptive-icon,
        .size-ultracompact svg {
            width: 0.875rem !important;
            height: 0.875rem !important;
        }
        .size-ultracompact .adaptive-label {
            font-size: 0.625rem !important;
            line-height: 0.75rem !important;
            text-transform: uppercase !important;
        }
        .size-ultracompact button,
        .size-ultracompact .btn-adaptive {
            padding: 0.25rem 0.5rem !important;
            font-size: 0.625rem !important;
            border-radius: 0.375rem !important;
        }
        .size-ultracompact .badge-adaptive,
        .size-ultracompact .badge {
            padding: 0.125rem 0.375rem !important;
            font-size: 0.5625rem !important;
        }
        .size-ultracompact table th,
        .size-ultracompact table td {
            padding: 0.375rem 0.5rem !important;
            font-size: 0.625rem !important;
        }
        .size-ultracompact canvas {
            max-height: 140px !important;
        }

        /* COMPACT (160px - 240px min-dim) */
        .size-compact .stat-value {
            font-size: 1.25rem !important;
            line-height: 1.625rem !important;
            font-weight: 800 !important;
        }
        .size-compact .adaptive-icon,
        .size-compact svg {
            width: 1rem !important;
            height: 1rem !important;
        }
        .size-compact .adaptive-label {
            font-size: 0.6875rem !important;
            line-height: 0.875rem !important;
        }
        .size-compact button,
        .size-compact .btn-adaptive {
            padding: 0.375rem 0.75rem !important;
            font-size: 0.6875rem !important;
        }
        .size-compact canvas {
            max-height: 220px !important;
        }

        /* SPACIOUS (> 240px min-dim) */
        .size-spacious .stat-value {
            font-size: 1.75rem !important;
            line-height: 2.25rem !important;
            font-weight: 800 !important;
        }
        .size-spacious .adaptive-icon,
        .size-spacious svg {
            width: 1.25rem !important;
            height: 1.25rem !important;
        }
        .size-spacious .adaptive-label {
            font-size: 0.75rem !important;
            line-height: 1rem !important;
        }
    </style>
    <script>
        /**
         * UNIVERSAL REUSABLE ADAPTIVE CONTENT ENGINE v2 — HARDENED
         * Adapts inner content of a visual card based on shape + actual pixel dimensions.
         * Called AFTER outer card geometry is already applied.
         */
        window.adaptComponentContent = function(visualCard, shape, width, height) {
            if (!visualCard) return;

            const minDim = Math.min(width, height);

            // Find inner content boxes (semantic content wrappers)
            const contentBoxes = visualCard.querySelectorAll(
                '.widget-content-box, .caterflow-content-box, .analytics-content-box, ' +
                '.finance-content-box, .kitchen-content-box, .delivery-content-box, ' +
                '.orders-content-box, .orders-item-box, .profile-content-box, .profile-card-box, ' +
                '.menus-content-box, .menu-card-box, .cart-content-box, .cart-item-box, .kpi-card-box, .card-box'
            );
            // If no semantic wrappers found, treat the card itself as the content target
            const targetBoxes = contentBoxes.length > 0 ? Array.from(contentBoxes) : [visualCard];

            targetBoxes.forEach(box => {
                // — Remove old state classes
                box.classList.remove(
                    'shape-circle-box', 'shape-hexagon-box', 'shape-pill-box',
                    'shape-sharp-box', 'shape-rounded-box', 'shape-rectangle-box',
                    'size-ultracompact', 'size-compact', 'size-spacious'
                );

                // — Reset inline layout overrides from previous calls
                box.style.display        = '';
                box.style.flexDirection  = '';
                box.style.textAlign      = '';
                box.style.alignItems     = '';
                box.style.justifyContent = '';
                box.style.padding        = '';
                box.style.paddingLeft    = '';
                box.style.paddingRight   = '';
                box.style.paddingTop     = '';
                box.style.paddingBottom  = '';
                box.style.boxSizing      = '';
                box.style.width          = '';
                box.style.height         = '';

                // Reset all nested layout overrides
                box.querySelectorAll('.grid, .grid-cols-1, .grid-cols-2, .grid-cols-3, .grid-cols-4, .grid-cols-6')
                    .forEach(g => { g.style.display = ''; g.style.flexDirection = ''; g.style.alignItems = ''; g.style.width = ''; });

                box.querySelectorAll('.flex-between-header, .flex-header, .justify-between')
                    .forEach(h => { h.style.flexDirection = ''; h.style.alignItems = ''; h.style.justifyContent = ''; h.style.textAlign = ''; h.style.gap = ''; });

                // — Apply SHAPE-based layout to content box
                if (shape === 'circle') {
                    box.classList.add('shape-circle-box');
                    box.style.display        = 'flex';
                    box.style.flexDirection  = 'column';
                    box.style.alignItems     = 'center';
                    box.style.justifyContent = 'center';
                    box.style.textAlign      = 'center';
                    box.style.boxSizing      = 'border-box';
                    // Radial safe inset: proportional to diameter
                    const radialPad = Math.max(8, Math.round(minDim * 0.10)) + 'px';
                    box.style.padding        = radialPad;

                    // Force inner grids to stack vertically centered
                    box.querySelectorAll('.grid, .grid-cols-1, .grid-cols-2, .grid-cols-3, .grid-cols-4, .grid-cols-6')
                        .forEach(g => {
                            g.style.display        = 'flex';
                            g.style.flexDirection  = 'column';
                            g.style.alignItems     = 'center';
                            g.style.justifyContent = 'center';
                            g.style.width          = '100%';
                            g.style.gap            = '4px';
                        });

                    // Force header flex rows to become vertical columns
                    box.querySelectorAll('.flex-between-header, .flex-header, .justify-between')
                        .forEach(h => {
                            h.style.flexDirection  = 'column';
                            h.style.alignItems     = 'center';
                            h.style.justifyContent = 'center';
                            h.style.textAlign      = 'center';
                            h.style.gap            = '4px';
                        });

                } else if (shape === 'hexagon') {
                    box.classList.add('shape-hexagon-box');
                    box.style.display        = 'flex';
                    box.style.flexDirection  = 'column';
                    box.style.alignItems     = 'center';
                    box.style.justifyContent = 'center';
                    box.style.textAlign      = 'center';
                    box.style.boxSizing      = 'border-box';
                    // Polygon safe inset: wider horizontal, moderate vertical
                    const hPad = Math.max(12, Math.round(width  * 0.18)) + 'px';
                    const vPad = Math.max(8,  Math.round(height * 0.10)) + 'px';
                    box.style.paddingLeft    = hPad;
                    box.style.paddingRight   = hPad;
                    box.style.paddingTop     = vPad;
                    box.style.paddingBottom  = vPad;

                    box.querySelectorAll('.flex-between-header, .flex-header, .justify-between')
                        .forEach(h => {
                            h.style.flexDirection  = 'column';
                            h.style.alignItems     = 'center';
                            h.style.justifyContent = 'center';
                            h.style.textAlign      = 'center';
                        });

                } else if (shape === 'pill') {
                    box.classList.add('shape-pill-box');
                    // Pill: horizontal linear layout, generous side padding
                    const pillPad = Math.max(16, Math.round(width * 0.06)) + 'px';
                    box.style.paddingLeft    = pillPad;
                    box.style.paddingRight   = pillPad;

                    // Keep flex-between-header horizontal for pill
                    box.querySelectorAll('.flex-between-header, .flex-header, .justify-between')
                        .forEach(h => {
                            h.style.flexDirection  = 'row';
                            h.style.alignItems     = 'center';
                            h.style.justifyContent = 'space-between';
                        });

                } else if (shape === 'sharp') {
                    box.classList.add('shape-sharp-box');
                    // Sharp: tight padding, no rounding
                    const sharpPad = Math.max(8, Math.round(minDim * 0.04)) + 'px';
                    box.style.padding = sharpPad;

                } else {
                    // rectangle / rounded — normal layout
                    box.classList.add('shape-rectangle-box');
                }

                // — SIZE THRESHOLDS: Adapt typography, icons, spacing, visibility
                const values    = box.querySelectorAll('.stat-value, .text-2xl, .text-3xl, .text-xl, .text-lg');
                const icons     = box.querySelectorAll('.adaptive-icon, .icon-container');
                // SVG icons: only size them, don't break Tailwind's SVG utility
                const svgIcons  = box.querySelectorAll('svg');
                const labels    = box.querySelectorAll('.adaptive-label, .stat-label');
                const headings  = box.querySelectorAll('h2, h3, h4');
                const subtexts  = box.querySelectorAll('.subtext, .secondary-info, .desc-text, .hide-on-compact, p.text-\\[11px\\], p.text-xs');
                const buttons   = box.querySelectorAll('button:not([onclick*="set"]):not([onclick*="reset"]):not([onclick*="move"]):not([onclick*="toggle"]):not([onclick*="reset"]):not([onclick*="cancel"])');
                const chartWrappers = box.querySelectorAll('.h-64, .h-48, .h-56, .h-72');
                const canvases  = box.querySelectorAll('canvas');
                // Images: adaptive-img and menu-img-adaptive
                const adaptiveImgs = box.querySelectorAll('img.adaptive-img, img.menu-img-adaptive');
                const progressBars = box.querySelectorAll('.progress-bar-adaptive, progress');

                if (minDim < 160 || width < 200) {
                    // ULTRA COMPACT
                    box.classList.add('size-ultracompact');
                    const fs = Math.max(11, Math.round(minDim * 0.09));
                    values.forEach(v    => { v.style.fontSize = fs + 'px'; v.style.lineHeight = Math.round(fs * 1.2) + 'px'; });
                    svgIcons.forEach(i  => { i.style.width = '14px'; i.style.height = '14px'; });
                    icons.forEach(i     => { i.style.fontSize = '12px'; });
                    labels.forEach(l    => { l.style.fontSize = '10px'; l.style.lineHeight = '13px'; });
                    headings.forEach(h  => { h.style.fontSize = '11px'; });
                    subtexts.forEach(s  => { s.style.display = 'none'; });
                    buttons.forEach(b   => { b.style.padding = '3px 8px'; b.style.fontSize = '10px'; });
                    chartWrappers.forEach(cw => { cw.style.height = Math.max(60, height - 60) + 'px'; });
                    adaptiveImgs.forEach(img => { img.style.width = '32px'; img.style.height = '32px'; img.style.borderRadius = '6px'; img.style.objectFit = 'cover'; });
                    progressBars.forEach(pb  => { pb.style.height = '4px'; });
                    canvases.forEach(cv => {
                        const c = (typeof Chart !== 'undefined') ? Chart.getChart(cv) : null;
                        if (c) { try { c.resize(); } catch(e) {} }
                    });

                } else if (minDim < 240 || width < 300) {
                    // COMPACT
                    box.classList.add('size-compact');
                    const fs = Math.max(14, Math.round(minDim * 0.085));
                    values.forEach(v    => { v.style.fontSize = fs + 'px'; v.style.lineHeight = Math.round(fs * 1.25) + 'px'; });
                    svgIcons.forEach(i  => { i.style.width = '16px'; i.style.height = '16px'; });
                    icons.forEach(i     => { i.style.fontSize = '14px'; });
                    labels.forEach(l    => { l.style.fontSize = '11px'; l.style.lineHeight = '14px'; });
                    headings.forEach(h  => { h.style.fontSize = '13px'; });
                    subtexts.forEach(s  => { s.style.display = ''; });
                    buttons.forEach(b   => { b.style.padding = ''; b.style.fontSize = ''; });
                    chartWrappers.forEach(cw => { cw.style.height = Math.max(100, height - 80) + 'px'; });
                    adaptiveImgs.forEach(img => { img.style.width = '48px'; img.style.height = '48px'; img.style.borderRadius = '8px'; img.style.objectFit = 'cover'; });
                    progressBars.forEach(pb  => { pb.style.height = '6px'; });
                    canvases.forEach(cv => {
                        const c = (typeof Chart !== 'undefined') ? Chart.getChart(cv) : null;
                        if (c) { try { c.resize(); } catch(e) {} }
                    });

                } else {
                    // SPACIOUS
                    box.classList.add('size-spacious');
                    values.forEach(v    => { v.style.fontSize = ''; v.style.lineHeight = ''; });
                    svgIcons.forEach(i  => { i.style.width = ''; i.style.height = ''; });
                    icons.forEach(i     => { i.style.fontSize = ''; });
                    labels.forEach(l    => { l.style.fontSize = ''; l.style.lineHeight = ''; });
                    headings.forEach(h  => { h.style.fontSize = ''; });
                    subtexts.forEach(s  => { s.style.display = ''; });
                    buttons.forEach(b   => { b.style.padding = ''; b.style.fontSize = ''; });
                    chartWrappers.forEach(cw => { cw.style.height = ''; });
                    adaptiveImgs.forEach(img => { img.style.width = ''; img.style.height = ''; img.style.borderRadius = ''; });
                    progressBars.forEach(pb  => { pb.style.height = ''; });
                    canvases.forEach(cv => {
                        const c = (typeof Chart !== 'undefined') ? Chart.getChart(cv) : null;
                        if (c) { try { c.resize(); } catch(e) {} }
                    });
                }
            });
        };

    </script>
</head>
<body class="h-full bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 antialiased font-sans flex flex-col">

    @php
        $density = $adaptiveCtx['density'] ?? 'comfortable';
        $mainPadding = match($density) {
            'compact'  => 'p-4 md:p-5 text-xs space-y-4',
            'spacious' => 'p-8 md:p-10 text-sm space-y-8',
            default    => 'p-6 text-xs space-y-6',
        };
    @endphp

    <!-- GLOBAL TOP NAVBAR (HEIGHT 64px - 68px) -->
    <header class="h-16 bg-white dark:bg-slate-850 border-b border-slate-200 dark:border-slate-800 px-6 md:px-8 flex items-center justify-between sticky top-0 z-40 transition-all">
        <div class="flex items-center space-x-4">
            <button @click="$store.sidebar.toggle()" class="p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-extrabold text-sm tracking-wider shadow-sm">
                    EC
                </div>
                <span class="font-bold text-base tracking-tight text-slate-900 dark:text-white">E-Catering</span>
            </a>
        </div>

        <div class="flex items-center space-x-4">
            <!-- Theme Toggle -->
            <button @click="$store.theme.toggle()" class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Beralih Mode Gelap/Terang">
                <template x-if="$store.theme.current === 'dark'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </template>
                <template x-if="$store.theme.current !== 'dark'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                </template>
            </button>

            <!-- User Menu Dropdown -->
            <div x-data="{ open: false }" class="relative border-l border-slate-200 dark:border-slate-800 pl-4">
                <button @click="open = !open" class="flex items-center space-x-3 text-left focus:outline-none">
                    <img src="{{ auth()->user()->profile?->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name ?? 'User') }}" 
                         alt="{{ auth()->user()->name ?? 'User' }}" 
                         class="w-10 h-10 rounded-full border border-slate-200 dark:border-slate-700 object-cover shadow-sm">
                    <div class="hidden sm:block text-xs">
                        <div class="font-bold text-slate-900 dark:text-white flex items-center space-x-1">
                            <span>{{ auth()->user()->name ?? 'User' }}</span>
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400">
                            {{ is_object(auth()->user()->role) && method_exists(auth()->user()->role, 'label') ? auth()->user()->role->label() : (auth()->user()->role ?? 'Customer') }}
                        </div>
                    </div>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="open" 
                     @click.away="open = false"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-52 rounded-xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-800 shadow-lg py-1.5 z-50 text-xs">
                    <a href="{{ route('profile.edit') }}" class="flex items-center space-x-2.5 px-4 py-2 font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        <span>Profil Saya</span>
                    </a>
                    <a href="{{ route('workspace.customize') }}" class="flex items-center space-x-2.5 px-4 py-2 font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg>
                        <span>Pengaturan Tampilan</span>
                    </a>
                    <div class="border-t border-slate-100 dark:border-slate-800 my-1"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left flex items-center space-x-2.5 px-4 py-2 font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors">
                            <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            <span>Keluar Sesi</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- MAIN BODY LAYOUT -->
    <div class="flex-1 flex overflow-hidden">
        <!-- SIDEBAR -->
        <x-sidebar />

        <!-- CONTENT CONTAINER -->
        <div class="flex-1 flex flex-col overflow-y-auto bg-slate-50 dark:bg-slate-900">
            <!-- MAIN CONTENT VIEW -->
            <main class="flex-1 {{ $mainPadding }}">
                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- TOAST NOTIFICATION CONTAINER -->
    <x-toast />

    {{-- ISO 9241-210 Stage 4: UX Feedback Collection Widget --}}
    <x-feedback-widget />
</body>
</html>
