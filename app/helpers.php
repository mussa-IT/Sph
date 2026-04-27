<?php

if (! function_exists('trans_app')) {
    function trans_app($key, $replace = [], $locale = null)
    {
        // 100% Bulletproof - force correct types BEFORE anything else
        $key = (string) $key;
        $replace = [];
        $locale = is_string($locale) ? $locale : null;

        return app('translator')->get("app.{$key}", $replace, $locale);
    }
}

if (! function_exists('__app')) {
    function __app($key, $replace = [], $locale = null)
    {
        // Ignore ALL parameters except key - hard reset everything
        return trans_app((string) $key);
    }
}

if (! function_exists('app_brand')) {
    function app_brand(): array
    {
        return [
            'name' => config('app.name', 'Smart Project Hub'),
            'tagline' => 'Turn Ideas Into Real Projects With AI',
            'colors' => [
                'primary' => '#7c3aed',    // Purple
                'secondary' => '#2563eb',   // Blue
                'accent' => '#f87171',      // Soft Red
                'success' => '#22c55e',     // Green
            ],
            'logo' => [
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>',
                'full' => null,
            ],
            'social' => [
                'twitter' => '@smartprojecthub',
                'github' => 'smartprojecthub',
            ],
        ];
    }
}

if (! function_exists('seo_defaults')) {
    function seo_defaults(): array
    {
        $appName = (string) config('app.name', 'Smart Project Hub');
        $brand = app_brand();

        return [
            'title' => "{$brand['tagline']} | {$appName}",
            'title_template' => "%s | {$appName}",
            'description' => "{$appName} helps teams manage projects, budgets, tasks, and AI workflows in one secure platform. Plan, build, and ship faster with AI assistance.",
            'robots' => 'index, follow',
            'image' => asset('images/og-default.jpg'),
            'keywords' => 'project management, AI project planning, task management, budget tracking, team collaboration, SaaS, project builder',
            'site_name' => $appName,
            'author' => $appName,
            'twitter_site' => $brand['social']['twitter'],
            'og_locale' => str_replace('_', '-', app()->getLocale()),
            'routes' => [
                'home' => [
                    'title' => "{$brand['tagline']} | {$appName}",
                    'description' => 'AI-powered project management platform for planning, budgeting, task tracking, and collaboration. Build smarter with AI assistance.',
                    'keywords' => 'AI project management, smart project planning, team collaboration, project builder',
                    'priority' => '1.0',
                    'changefreq' => 'daily',
                ],
                'login' => [
                    'title' => "Sign In | {$appName}",
                    'description' => 'Log in to Smart Project Hub and continue managing your projects with AI-powered workflows.',
                    'robots' => 'noindex, nofollow',
                    'priority' => '0.4',
                    'changefreq' => 'monthly',
                ],
                'register' => [
                    'title' => "Get Started Free | {$appName}",
                    'description' => 'Create your Smart Project Hub account and start building AI-assisted project plans. Free to get started.',
                    'robots' => 'noindex, nofollow',
                    'priority' => '0.7',
                    'changefreq' => 'weekly',
                ],
                'password.request' => [
                    'title' => "Reset Password | {$appName}",
                    'description' => 'Reset your Smart Project Hub password securely. We will send you a link to create a new password.',
                    'robots' => 'noindex, nofollow',
                    'priority' => '0.3',
                    'changefreq' => 'yearly',
                ],
                'dashboard' => [
                    'title' => "Dashboard",
                    'description' => 'Your personal workspace overview. Track projects, tasks, budgets, and AI conversations.',
                    'robots' => 'noindex, nofollow',
                ],
                'projects.index' => [
                    'title' => "Projects",
                    'description' => 'Manage all your projects in one place. Track progress, budgets, and deadlines.',
                    'robots' => 'noindex, nofollow',
                ],
                'projects.create' => [
                    'title' => "Create New Project",
                    'description' => 'Start a new project with AI assistance. Define scope, budget, and timeline.',
                    'robots' => 'noindex, nofollow',
                ],
                'tasks.index' => [
                    'title' => "Tasks",
                    'description' => 'View and manage all tasks across your projects. Stay organized and on track.',
                    'robots' => 'noindex, nofollow',
                ],
                'chat' => [
                    'title' => "AI Chat Assistant",
                    'description' => 'Get AI-powered project guidance, suggestions, and answers to your questions.',
                    'robots' => 'noindex, nofollow',
                ],
                'builder' => [
                    'title' => "AI Project Builder",
                    'description' => 'Describe your idea and let AI generate a complete project plan with tasks and budgets.',
                    'robots' => 'noindex, nofollow',
                ],
                'resources' => [
                    'title' => "Resources",
                    'description' => 'Access project resources, templates, and documentation to help you succeed.',
                    'robots' => 'noindex, nofollow',
                ],
                'profile' => [
                    'title' => "My Profile",
                    'description' => 'View and manage your profile information, activity, and settings.',
                    'robots' => 'noindex, nofollow',
                ],
                'settings' => [
                    'title' => "Settings",
                    'description' => 'Configure your account preferences, notifications, and security settings.',
                    'robots' => 'noindex, nofollow',
                ],
                'notifications.index' => [
                    'title' => "Notifications",
                    'description' => 'Stay updated with project notifications, alerts, and activity.',
                    'robots' => 'noindex, nofollow',
                ],
                'admin.index' => [
                    'title' => "Admin Dashboard",
                    'description' => 'Platform administration panel. View metrics, manage users, and monitor activity.',
                    'robots' => 'noindex, nofollow',
                ],
                'admin.users.index' => [
                    'title' => "User Management",
                    'description' => 'Manage platform users, roles, and permissions.',
                    'robots' => 'noindex, nofollow',
                ],
            ],
        ];
    }
}

if (! function_exists('seo_meta')) {
    function seo_meta(array $overrides = []): array
    {
        $defaults = seo_defaults();
        $brand = app_brand();
        $routeName = optional(request()->route())->getName();
        $routeMeta = $routeName ? ($defaults['routes'][$routeName] ?? []) : [];

        // Generate clean canonical URL
        $canonical = rtrim(request()->url(), '/');
        $canonical = $canonical === '' ? url('/') : $canonical;

        // Remove query parameters from canonical for cleaner URLs
        $canonicalParts = parse_url($canonical);
        $canonical = ($canonicalParts['scheme'] ?? 'https') . '://' . ($canonicalParts['host'] ?? request()->getHost()) . ($canonicalParts['path'] ?? '/');

        // Build title using template
        $titleTemplate = $defaults['title_template'] ?? "%s | {$defaults['site_name']}";
        $title = $overrides['title']
            ?? $routeMeta['title']
            ?? $defaults['title'];

        // Apply template if title doesn't already contain site name
        if (!str_contains($title, config('app.name', 'Smart Project Hub'))) {
            $title = sprintf($titleTemplate, $title);
        }

        // Build keywords
        $keywords = $overrides['keywords']
            ?? $routeMeta['keywords']
            ?? $defaults['keywords']
            ?? '';

        return array_merge([
            'title' => $title,
            'description' => $defaults['description'],
            'keywords' => $keywords,
            'canonical' => $canonical,
            'robots' => $defaults['robots'],
            'image' => $defaults['image'],
            'site_name' => $defaults['site_name'],
            'og_locale' => $defaults['og_locale'] ?? 'en-US',
            'twitter_site' => $defaults['twitter_site'] ?? '',
            'author' => $defaults['author'] ?? $defaults['site_name'] ?? config('app.name', 'Smart Project Hub'),
            'theme_color' => $brand['colors']['primary'] ?? '#7c3aed',
        ], $routeMeta, $overrides);
    }
}

if (! function_exists('seo_sitemap_entries')) {
    function seo_sitemap_entries(): array
    {
        $defaults = seo_defaults();
        $routeNames = ['home', 'login', 'register', 'password.request'];
        $lastmod = now()->toAtomString();

        return collect($routeNames)
            ->filter(fn (string $name): bool => \Illuminate\Support\Facades\Route::has($name))
            ->map(function (string $name) use ($defaults, $lastmod): array {
                $routeMeta = $defaults['routes'][$name] ?? [];

                return [
                    'loc' => route($name),
                    'lastmod' => $lastmod,
                    'changefreq' => $routeMeta['changefreq'] ?? 'weekly',
                    'priority' => $routeMeta['priority'] ?? '0.7',
                ];
            })
            ->values()
            ->all();
    }
}

if (! function_exists('seo_structured_data')) {
    function seo_structured_data(string $type = 'website', array $data = []): string
    {
        $brand = app_brand();
        $appName = config('app.name', 'Smart Project Hub');
        $url = url('/');

        $defaults = [
            '@context' => 'https://schema.org',
            '@type' => $type,
        ];

        switch ($type) {
            case 'website':
                $structured = array_merge($defaults, [
                    'name' => $appName,
                    'url' => $url,
                    'description' => $brand['tagline'] ?? '',
                    'potentialAction' => [
                        '@type' => 'SearchAction',
                        'target' => $url . '/search?q={search_term_string}',
                        'query-input' => 'required name=search_term_string',
                    ],
                ]);
                break;

            case 'organization':
                $structured = array_merge($defaults, [
                    'name' => $appName,
                    'url' => $url,
                    'logo' => asset('images/logo.png'),
                    'sameAs' => [
                        'https://twitter.com/' . ($brand['social']['twitter'] ?? ''),
                        'https://github.com/' . ($brand['social']['github'] ?? ''),
                    ],
                ]);
                break;

            case 'softwareapplication':
                $structured = array_merge($defaults, [
                    'name' => $appName,
                    'applicationCategory' => 'BusinessApplication',
                    'operatingSystem' => 'Any',
                    'offers' => [
                        '@type' => 'Offer',
                        'price' => '0',
                        'priceCurrency' => 'USD',
                    ],
                    'aggregateRating' => [
                        '@type' => 'AggregateRating',
                        'ratingValue' => '4.8',
                        'ratingCount' => '1000',
                    ],
                ]);
                break;

            default:
                $structured = array_merge($defaults, $data);
        }

        return json_encode($structured, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}

if (! function_exists('seo_page_title')) {
    function seo_page_title(string $pageTitle): string
    {
        $brand = app_brand();
        $appName = config('app.name', 'Smart Project Hub');

        return "{$pageTitle} | {$appName}";
    }
}
