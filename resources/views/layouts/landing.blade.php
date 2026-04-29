<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @php
            $brand = app_brand();
            $defaultSeo = seo_meta();
            $seo = seo_meta([
                'title' => trim($__env->yieldContent('title')) ?: $defaultSeo['title'],
                'description' => trim($__env->yieldContent('meta_description')) ?: $defaultSeo['description'],
                'keywords' => trim($__env->yieldContent('meta_keywords')) ?: $defaultSeo['keywords'],
                'canonical' => trim($__env->yieldContent('canonical')) ?: $defaultSeo['canonical'],
                'image' => trim($__env->yieldContent('meta_image')) ?: $defaultSeo['image'],
            ]);
        @endphp
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />

        {{-- Primary Meta Tags --}}
        <title>{{ $seo['title'] }}</title>
        <meta name="title" content="{{ $seo['title'] }}">
        <meta name="description" content="{{ $seo['description'] }}" />
        <meta name="keywords" content="{{ $seo['keywords'] }}" />
        <meta name="author" content="{{ $seo['author'] }}" />
        <meta name="robots" content="{{ $seo['robots'] }}" />
        <link rel="canonical" href="{{ $seo['canonical'] }}" />

        {{-- Open Graph / Facebook --}}
        <meta property="og:type" content="website" />
        <meta property="og:url" content="{{ $seo['canonical'] }}" />
        <meta property="og:title" content="{{ $seo['title'] }}" />
        <meta property="og:description" content="{{ $seo['description'] }}" />
        <meta property="og:image" content="{{ $seo['image'] }}" />
        <meta property="og:site_name" content="{{ $seo['site_name'] }}" />
        <meta property="og:locale" content="{{ $seo['og_locale'] }}" />

        {{-- Twitter Card --}}
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:url" content="{{ $seo['canonical'] }}" />
        <meta name="twitter:title" content="{{ $seo['title'] }}" />
        <meta name="twitter:description" content="{{ $seo['description'] }}" />
        <meta name="twitter:image" content="{{ $seo['image'] }}" />
        <meta name="twitter:site" content="{{ $seo['twitter_site'] }}" />

        {{-- Favicons --}}
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.svg') }}">

        {{-- PWA --}}
        <meta name="application-name" content="{{ $seo['site_name'] }}">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

        {{-- Theme Color --}}
        <meta name="theme-color" content="{{ $brand['colors']['primary'] }}" media="(prefers-color-scheme: light)">
        <meta name="theme-color" content="#0f172a" media="(prefers-color-scheme: dark)">
        <meta name="msapplication-TileColor" content="{{ $brand['colors']['primary'] }}" />

        {{-- Structured Data --}}
        <script type="application/ld+json">
        {!! seo_structured_data('website') !!}
        </script>
        <script type="application/ld+json">
        {!! seo_structured_data('organization') !!}
        </script>

        <link href="{{ asset('app-styles.css') }}" rel="stylesheet">
        <link href="{{ asset('grey-background-theme.css') }}" rel="stylesheet">
        <link href="{{ asset('professional-cards.css') }}" rel="stylesheet">
        <link href="{{ asset('card-visibility-fix.css') }}" rel="stylesheet">
        {{-- Temporarily disabled Vite while fixing dependency issues --}}
        {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <style>
            @keyframes gradientMove {
                0% { background-position: 0% 50%; }
                50% { background-position: 100% 50%; }
                100% { background-position: 0% 50%; }
            }
            .animate-gradient {
                background-size: 200% 200%;
                animation: gradientMove 8s ease infinite;
            }
            .scroll-reveal {
                opacity: 0;
                transform: translateY(32px);
                transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
            }
            .scroll-reveal.revealed {
                opacity: 1;
                transform: translateY(0);
            }
            .scroll-reveal-delay-1 { transition-delay: 0.1s; }
            .scroll-reveal-delay-2 { transition-delay: 0.2s; }
            .scroll-reveal-delay-3 { transition-delay: 0.3s; }
            .scroll-reveal-delay-4 { transition-delay: 0.4s; }
            .scroll-reveal-delay-5 { transition-delay: 0.5s; }
            .feature-card:hover .feature-icon {
                transform: scale(1.1) rotate(-3deg);
            }
            .btn-gradient {
                position: relative;
                overflow: hidden;
            }
            .btn-gradient::before {
                content: '';
                position: absolute;
                top: 0; left: -100%;
                width: 100%; height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
                transition: left 0.6s ease;
            }
            .btn-gradient:hover::before {
                left: 100%;
            }
        </style>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
        <style>body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }</style>
    </head>
    <body class="bg-background text-foreground antialiased dark:bg-background-dark dark:text-foreground-dark"
          x-data="{ scrolled: false, mobileMenuOpen: false }"
          @scroll.window="scrolled = (window.pageYOffset > 20)">
        @yield('content')
    </body>
</html>
