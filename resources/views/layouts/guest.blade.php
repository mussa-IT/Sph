<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @php
            $defaultSeo = seo_meta(['robots' => 'noindex, nofollow']);
            $seo = seo_meta([
                'title' => trim($__env->yieldContent('title'))
                    ? trim($__env->yieldContent('title')).' - '.config('app.name', 'Sph')
                    : $defaultSeo['title'],
                'description' => trim($__env->yieldContent('meta_description')) ?: $defaultSeo['description'],
                'canonical' => trim($__env->yieldContent('canonical')) ?: $defaultSeo['canonical'],
                'robots' => trim($__env->yieldContent('meta_robots')) ?: $defaultSeo['robots'],
            ]);
        @endphp
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>{{ $seo['title'] }}</title>
        <meta name="description" content="{{ $seo['description'] }}">
        <meta name="robots" content="{{ $seo['robots'] }}">
        <link rel="canonical" href="{{ $seo['canonical'] }}">
        <meta property="og:type" content="website">
        <meta property="og:title" content="{{ $seo['title'] }}">
        <meta property="og:description" content="{{ $seo['description'] }}">
        <meta property="og:url" content="{{ $seo['canonical'] }}">
        <meta property="og:site_name" content="{{ $seo['site_name'] }}">
        <meta name="twitter:card" content="summary">
        <meta name="twitter:title" content="{{ $seo['title'] }}">
        <meta name="twitter:description" content="{{ $seo['description'] }}">
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link href="{{ asset('modern-auth.css') }}" rel="stylesheet">
        {{-- Temporarily disabled Vite while fixing dependency issues --}}
        {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
        <style>body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }</style>
    </head>
    <body>
        <div class="auth-card">
            <span class="auth-badge">Smart Project Hub</span>
            <h2 class="auth-title">@yield('heading')</h2>
            <p class="auth-subtitle">@yield('subtitle', 'Enter your credentials to access your account')</p>

            @yield('content')

            <p class="auth-footer">
                Compatible with Gmail, Outlook Web, LinkedIn and most web editors for a smooth project management experience anywhere online.
            </p>
        </div>

        <script>
            // Initialize dark mode on page load for guest pages
            document.addEventListener('DOMContentLoaded', function() {
                var html = document.documentElement;
                var isDark = localStorage.getItem('darkMode') === 'true';
                if (isDark) {
                    html.classList.add('dark');
                } else {
                    html.classList.remove('dark');
                }
                updateGuestDarkModeUI(isDark);
            });

            function guestToggleDarkMode(button) {
                var html = document.documentElement;
                var isDark = html.classList.toggle('dark');
                localStorage.setItem('darkMode', isDark);
                updateGuestDarkModeUI(isDark);
            }

            function updateGuestDarkModeUI(isDark) {
                var icon = document.getElementById('guestDarkModeIcon');
                var label = document.getElementById('guestDarkModeLabel');
                if (icon && label) {
                    icon.textContent = isDark ? '☀️' : '🌙';
                    label.textContent = isDark ? 'Light' : 'Dark';
                }
            }
        </script>
    </body>
</html>
