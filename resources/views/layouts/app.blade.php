<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @php
            $defaultSeo = seo_meta(['robots' => 'noindex, nofollow']);
            $seo = seo_meta([
                'title' => trim($__env->yieldContent('title'))
                    ? trim($__env->yieldContent('title')).' | '.config('app.name', 'Smart Project Hub')
                    : $defaultSeo['title'],
                'description' => trim($__env->yieldContent('meta_description')) ?: $defaultSeo['description'],
                'keywords' => trim($__env->yieldContent('meta_keywords')) ?: $defaultSeo['keywords'],
                'canonical' => trim($__env->yieldContent('canonical')) ?: $defaultSeo['canonical'],
                'robots' => trim($__env->yieldContent('meta_robots')) ?: $defaultSeo['robots'],
                'image' => trim($__env->yieldContent('meta_image')) ?: $defaultSeo['image'],
            ]);
        @endphp
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- Primary Meta Tags --}}
        <title>{{ $seo['title'] }}</title>
        <meta name="title" content="{{ $seo['title'] }}">
        <meta name="description" content="{{ $seo['description'] }}">
        <meta name="keywords" content="{{ $seo['keywords'] }}">
        <meta name="author" content="{{ $seo['author'] }}">
        <meta name="robots" content="{{ $seo['robots'] }}">
        <meta name="theme-color" content="{{ $seo['theme_color'] }}">
        <link rel="canonical" href="{{ $seo['canonical'] }}">

        {{-- Open Graph / Facebook --}}
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ $seo['canonical'] }}">
        <meta property="og:title" content="{{ $seo['title'] }}">
        <meta property="og:description" content="{{ $seo['description'] }}">
        <meta property="og:image" content="{{ $seo['image'] }}">
        <meta property="og:site_name" content="{{ $seo['site_name'] }}">
        <meta property="og:locale" content="{{ $seo['og_locale'] }}">

        {{-- Twitter --}}
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:url" content="{{ $seo['canonical'] }}">
        <meta name="twitter:title" content="{{ $seo['title'] }}">
        <meta name="twitter:description" content="{{ $seo['description'] }}">
        <meta name="twitter:image" content="{{ $seo['image'] }}">
        <meta name="twitter:site" content="{{ $seo['twitter_site'] }}">

        {{-- Favicons --}}
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.svg') }}">

        {{-- PWA --}}
        <meta name="application-name" content="{{ $seo['site_name'] }}">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

        {{-- Structured Data --}}
        <script type="application/ld+json">
        {!! seo_structured_data('softwareapplication') !!}
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/web3/Web3App.jsx'])
        <link rel="dns-prefetch" href="https://cdn.jsdelivr.net" />
        <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin />
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <style>
            @keyframes fadeInUp {
                from { opacity: 0; transform: translateY(16px); }
                to   { opacity: 1; transform: translateY(0); }
            }
            @keyframes fadeIn {
                from { opacity: 0; }
                to   { opacity: 1; }
            }
            .animate-fade-in-up {
                opacity: 0;
                animation: fadeInUp 0.5s ease-out forwards;
            }
            .animate-fade-in {
                opacity: 0;
                animation: fadeIn 0.4s ease-out forwards;
            }
            .animate-delay-100 { animation-delay: 0.10s; }
            .animate-delay-200 { animation-delay: 0.20s; }
            .animate-delay-300 { animation-delay: 0.30s; }
            .animate-delay-400 { animation-delay: 0.40s; }
            .animate-delay-500 { animation-delay: 0.50s; }
        </style>
    </head>
    <body class="bg-background text-foreground dark:bg-background-dark dark:text-foreground-dark">
        <div class="relative flex min-h-screen flex-col">
            <!-- Navbar -->
            @include('components.navbar')

            <div class="flex-1">
                <div class="mx-auto grid h-full max-w-7xl grid-cols-1 gap-4 px-3 py-3 sm:px-4 sm:py-4 sm:gap-6 md:px-6 md:py-6 lg:grid-cols-[280px_minmax(0,1fr)] lg:px-8 lg:py-8 lg:gap-8">
                    <aside class="hidden lg:block order-2 lg:order-1">
                        <x-sidebar />
                    </aside>
                    <!-- Mobile sidebar portal -->
                    <div class="lg:hidden fixed inset-0 z-40 hidden" id="mobile-sidebar-portal">
                        <x-sidebar />
                    </div>

                    <section class="order-1 lg:order-2 min-w-0">
                        <div class="surface-card interactive-lift bg-background-secondary dark:bg-background-secondary-dark">
                            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-kicker">{{ $pageHeading ?? 'Dashboard' }}</p>
                                    <h1 class="heading-xl mt-2">{{ $pageTitle ?? 'Welcome back' }}</h1>
                                </div>
                                <div class="flex flex-wrap gap-2 sm:gap-3">
                                    <button class="btn-brand px-4 sm:px-5">New report</button>
                                    <button class="btn-brand-muted px-4 sm:px-5">Export</button>
                                </div>
                            </div>

                            <div class="space-y-6">
                                @yield('content')
                            </div>
                        </div>

                        <footer class="mt-6 rounded-[2rem] border border-white/10 bg-slate-950/85 p-6 text-sm text-slate-400 shadow-[var(--shadow-premium)]">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <p>© {{ date('Y') }} {{ config('app.name', 'Sph') }}. Crafted for modern SaaS products.</p>
                                <div class="flex flex-wrap gap-3">
                                    <a href="#" class="hover:text-white">Privacy</a>
                                    <a href="#" class="hover:text-white">Terms</a>
                                    <a href="#" class="hover:text-white">Contact</a>
                                </div>
                            </div>
                        </footer>
                    </section>
                </div>
            </div>
        </div>

        {{-- Premium Wow Factor Features --}}
        <x-command-palette />
        <x-keyboard-shortcuts />
    </body>
</html>
