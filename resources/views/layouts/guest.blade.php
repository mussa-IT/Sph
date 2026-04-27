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
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
        <style>body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }</style>
    </head>
    <body class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(124,58,237,0.15),_transparent_25%),linear-gradient(135deg,#f8fafc,#e2e8f0,#cbd5e1)] text-foreground dark:bg-[radial-gradient(circle_at_top_left,_rgba(124,58,237,0.15),_transparent_25%),linear-gradient(135deg,#0f172a,#1e293b,#334155)] dark:text-foreground-dark">
        <div class="min-h-screen flex items-center justify-center px-4 py-6">
            <div class="w-full max-w-4xl overflow-hidden rounded-2xl border border-muted/20 bg-background shadow-[var(--shadow-premium)] backdrop-blur-xl dark:border-muted-dark/20 dark:bg-background-dark">
                <div class="grid lg:grid-cols-[1fr_1fr]">
                    <section class="hidden lg:flex flex-col justify-center gap-6 bg-gradient-to-br from-primary via-secondary to-background-secondary px-8 py-10 text-primary-foreground dark:from-primary dark:via-secondary dark:to-background-secondary-dark">
                        <div class="space-y-3">
                            <h1 class="text-3xl font-semibold leading-tight tracking-tight">Welcome Back</h1>
                            <p class="text-primary-foreground/80 leading-relaxed text-sm">Sign in to manage your projects, track tasks, and collaborate with your team.</p>
                        </div>

                        <div class="space-y-4 text-sm">
                            <div class="flex items-start gap-3">
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-primary-foreground/20 text-xs">✓</span>
                                <p class="text-primary-foreground/80">AI-powered project planning</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-primary-foreground/20 text-xs">✓</span>
                                <p class="text-primary-foreground/80">Real-time collaboration</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-primary-foreground/20 text-xs">✓</span>
                                <p class="text-primary-foreground/80">Secure cloud storage</p>
                            </div>
                        </div>
                    </section>

                    <main class="p-6 sm:p-8 bg-background-secondary dark:bg-background-secondary-dark">
                        <div class="mb-6 flex items-center justify-between">
                            <div>
                                <p class="text-xs font-medium text-muted uppercase tracking-wider">{{ $section ?? 'Account' }}</p>
                                <h2 class="text-2xl font-bold mt-1">@yield('heading')</h2>
                            </div>
                            <button
                                type="button"
                                id="guestDarkModeToggle"
                                onclick="guestToggleDarkMode(this)"
                                class="inline-flex h-9 items-center justify-center rounded-xl border border-muted/20 bg-background px-3 text-sm font-medium text-foreground transition hover:bg-muted/10 dark:border-muted-dark/20 dark:bg-background-dark dark:text-foreground-dark"
                                aria-label="Toggle dark mode"
                            >
                                <span id="guestDarkModeIcon">☀️</span>
                            </button>
                        </div>

                        <div class="rounded-xl border border-muted/20 bg-background p-6 shadow-sm dark:border-muted-dark/20 dark:bg-background-dark">
                            @yield('content')
                        </div>
                    </main>
                </div>
            </div>
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
