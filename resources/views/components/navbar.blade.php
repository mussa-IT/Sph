@php
    /** @var \App\Models\User|null $authUser */
    $authUser = auth()->user();
@endphp

<div class="bg-background border-b border-muted/20 text-foreground backdrop-blur-xl dark:bg-background-dark dark:border-muted-dark/20 dark:text-foreground-dark relative z-40">
    <script>
        window.authUser = @json($authUser);
    </script>
    <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
        <div class="flex items-center gap-3">
            <!-- Mobile sidebar toggle -->
            <button
                @click="$dispatch('toggle-sidebar')"
                class="touch-target lg:hidden inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-muted/20 bg-background-secondary text-foreground transition-all duration-200 ease-out hover:bg-muted/10 hover:scale-[1.05] active:scale-[0.95] dark:border-muted-dark/20 dark:bg-background-secondary-dark dark:text-foreground-dark dark:hover:bg-muted-dark/10"
                aria-label="Open menu"
            >
                ☰
            </button>

            <a href="/" class="text-foreground transition hover:text-primary dark:text-foreground-dark dark:hover:text-primary">
                <x-brand-logo
                    class="hidden sm:inline-flex"
                    badge-size="h-11 w-11"
                    badge-text-size="text-base"
                    name-size="text-sm"
                    subtitle="SaaS dashboard"
                    subtitle-size="text-xs"
                />
                <x-brand-logo
                    class="inline-flex sm:hidden"
                    :compact="true"
                    badge-size="h-11 w-11"
                    badge-text-size="text-base"
                />
            </a>
        </div>

        <label class="relative block w-full max-w-xl lg:max-w-md xl:max-w-xl lg:flex-1 lg:mx-6">
            <span class="sr-only">Search</span>
            <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-muted dark:text-muted-dark">🔍</span>
            <input
                type="search"
                placeholder="{{ __app('search') }}"
                class="h-12 w-full rounded-2xl border border-muted/20 bg-background px-12 text-sm text-foreground outline-none transition duration-200 placeholder:text-muted focus:border-primary focus:ring-4 focus:ring-primary/10 dark:border-muted-dark/20 dark:bg-background-dark dark:text-foreground-dark"
            />
        </label>

        <div class="flex flex-wrap items-center justify-end gap-2 sm:gap-3">
            <!-- Notifications Dropdown -->
            <details class="group relative">
                <summary class="touch-target inline-flex h-12 cursor-pointer list-none items-center justify-center rounded-2xl border border-muted/20 bg-background px-4 text-sm font-semibold text-foreground transition-all duration-200 ease-out hover:bg-muted/10 hover:scale-[1.03] active:scale-[0.97] dark:border-muted-dark/20 dark:bg-background-dark dark:text-foreground-dark dark:hover:bg-muted-dark/10">
                    <span class="mr-2 text-base">🔔</span>
                    <span id="notification-count" class="hidden rounded-full bg-primary px-2 py-0.5 text-xs font-bold text-primary-foreground"></span>
                    {{ __app('notifications') }}
                    <span class="ml-2 text-muted dark:text-muted-dark">▾</span>
                </summary>
                <div class="absolute right-0 z-[9999] mt-3 w-[min(20rem,calc(100vw-1rem))] rounded-2xl border border-muted/20 bg-background/95 p-4 shadow-2xl backdrop-blur-xl dark:border-muted-dark/20 dark:bg-background-dark/95">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-sm font-semibold text-foreground dark:text-foreground-dark">{{ __app('notifications') }}</p>
                        <button id="mark-all-read" class="text-xs text-primary hover:text-primary/80">{{ __app('mark_all_read') }}</button>
                    </div>
                    <div id="notifications-list" class="space-y-2 max-h-96 overflow-y-auto">
                        <!-- Notifications will be loaded here -->
                        <div class="text-center py-8 text-muted dark:text-muted-dark">
                            <div class="text-2xl mb-2">📭</div>
                            <p class="text-sm">{{ __app('no_notifications') }}</p>
                        </div>
                    </div>
                    <a
                        href="{{ route('notifications.index') }}"
                        class="mt-3 inline-flex min-h-[40px] w-full items-center justify-center rounded-xl border border-muted/20 bg-background-secondary px-3 py-2 text-xs font-semibold text-foreground transition hover:bg-muted/10 dark:border-muted-dark/20 dark:bg-background-secondary-dark dark:text-foreground-dark dark:hover:bg-muted-dark/10"
                    >
                        View all notifications
                    </a>
                </div>
            </details>

            <details class="group relative">
                <summary class="touch-target inline-flex h-12 cursor-pointer list-none items-center justify-center rounded-2xl border border-muted/20 bg-background px-4 text-sm font-semibold text-foreground transition-all duration-200 ease-out hover:bg-muted/10 hover:scale-[1.03] active:scale-[0.97] dark:border-muted-dark/20 dark:bg-background-dark dark:text-foreground-dark dark:hover:bg-muted-dark/10">
                    <span class="mr-2">🌐</span>
                    {{ __app('language') }}
                    <span class="ml-2 text-muted dark:text-muted-dark">▾</span>
                </summary>
                <div class="absolute right-0 z-[9999] mt-3 w-48 rounded-2xl border border-muted/20 bg-background/95 p-3 shadow-2xl backdrop-blur-xl dark:border-muted-dark/20 dark:bg-background-dark/95">
                    <p class="mb-2 text-xs uppercase tracking-[.3em] text-muted dark:text-muted-dark">{{ __app('language') }}</p>
                    <ul class="space-y-2">
                        <li class="rounded-xl bg-muted/10 px-3 py-2 dark:bg-muted-dark/10">
                            <a href="{{ route('language.switch', 'en') }}" class="block w-full">{{ __app('english') }}</a>
                        </li>
                        <li class="rounded-xl bg-muted/10 px-3 py-2 dark:bg-muted-dark/10">
                            <a href="{{ route('language.switch', 'sw') }}" class="block w-full">{{ __app('swahili') }}</a>
                        </li>
                    </ul>
                </div>
            </details>

            <button
                type="button"
                id="navbarDarkModeToggle"
                onclick="smartNavbarToggleDarkMode(this)"
                class="touch-target inline-flex h-12 items-center justify-center rounded-2xl border border-muted/20 bg-background px-4 text-sm font-semibold text-foreground transition-all duration-200 ease-out hover:bg-muted/10 hover:scale-[1.03] active:scale-[0.97] dark:border-muted-dark/20 dark:bg-background-dark dark:text-foreground-dark dark:hover:bg-muted-dark/10"
                aria-label="Toggle dark mode"
            >
                <span class="mr-2" id="navbarDarkModeIcon">🌙</span>
                <span id="navbarDarkModeLabel">Dark</span>
            </button>

            <!-- Wallet Connection -->
            <div id="wallet-connect-container"></div>

            <details class="group relative">
                <summary class="touch-target inline-flex h-12 cursor-pointer list-none items-center justify-center rounded-2xl border border-muted/20 bg-background px-4 text-sm font-semibold text-foreground transition-all duration-200 ease-out hover:bg-muted/10 hover:scale-[1.03] active:scale-[0.97] dark:border-muted-dark/20 dark:bg-background-dark dark:text-foreground-dark dark:hover:bg-muted-dark/10">
                    <span class="mr-2">👤</span>
                    Account
                    <span class="ml-2 text-muted dark:text-muted-dark">▾</span>
                </summary>
                <div class="absolute right-0 z-[9999] mt-3 w-[min(14rem,calc(100vw-1rem))] rounded-2xl border border-muted/20 bg-background/95 p-4 shadow-2xl backdrop-blur-xl dark:border-muted-dark/20 dark:bg-background-dark/95">
                    <div class="space-y-3">
                        <!-- Google Connection Status -->
                        @if(filled($authUser?->google_id))
                            <div class="flex items-center gap-2 rounded-xl bg-emerald-500/10 px-3 py-2 dark:bg-emerald-500/20">
                                <span class="text-emerald-600 dark:text-emerald-400">🔗</span>
                                <span class="text-xs font-medium text-emerald-700 dark:text-emerald-300">Google Connected</span>
                            </div>
                        @else
                            <div class="flex items-center gap-2 rounded-xl bg-amber-500/10 px-3 py-2 dark:bg-amber-500/20">
                                <span class="text-amber-600 dark:text-amber-400">⚠️</span>
                                <span class="text-xs font-medium text-amber-700 dark:text-amber-300">Google Not Connected</span>
                            </div>
                        @endif

                        <hr class="border-muted/20 dark:border-muted-dark/20">

                        <a href="{{ route('profile') }}" class="block rounded-xl px-3 py-2 text-sm text-foreground transition hover:bg-muted/10 dark:text-foreground-dark dark:hover:bg-muted-dark/10">{{ __app('profile') }}</a>
                        <a href="{{ route('settings') }}" class="block rounded-xl px-3 py-2 text-sm text-foreground transition hover:bg-muted/10 dark:text-foreground-dark dark:hover:bg-muted-dark/10">{{ __app('settings') }}</a>
                        <a href="#" class="block rounded-xl px-3 py-2 text-sm text-foreground transition hover:bg-muted/10 dark:text-foreground-dark dark:hover:bg-muted-dark/10">Help center</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full rounded-xl bg-warning/10 px-3 py-2 text-left text-sm font-semibold text-warning transition hover:bg-warning/20 dark:bg-warning/10 dark:text-warning dark:hover:bg-warning/20">{{ __app('logout') }}</button>
                        </form>
                    </div>
                </div>
            </details>
        </div>
    </div>

    @once
        <style>
            /* Ensure dropdowns appear above all content */
            .group[open] > div {
                z-index: 9999 !important;
                position: absolute !important;
                transform: translateY(0) !important;
                opacity: 1 !important;
                visibility: visible !important;
            }
            
            /* Force dropdown visibility */
            details[open] > div {
                display: block !important;
                z-index: 9999 !important;
            }
            
            /* Ensure dropdown content is visible */
            .group > div {
                background: rgba(255, 255, 255, 0.95) !important;
                backdrop-filter: blur(16px) !important;
                -webkit-backdrop-filter: blur(16px) !important;
            }
            
            .dark .group > div {
                background: rgba(15, 23, 42, 0.95) !important;
            }
        </style>
        <script>
            // Theme management with user preferences and system detection
            const themeManager = {
                userPreference: '{{ $authUser->theme_preference ?? 'system' }}',
                systemPreference: window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light',

                init() {
                    this.applyTheme();
                    this.setupSystemPreferenceListener();
                },

                applyTheme() {
                    const html = document.documentElement;
                    const shouldBeDark = this.shouldUseDarkMode();

                    if (shouldBeDark) {
                        html.classList.add('dark');
                    } else {
                        html.classList.remove('dark');
                    }

                    localStorage.setItem('theme_preference', this.userPreference);
                    this.updateUI(shouldBeDark);
                },

                shouldUseDarkMode() {
                    switch (this.userPreference) {
                        case 'dark':
                            return true;
                        case 'light':
                            return false;
                        case 'system':
                        default:
                            return this.systemPreference;
                    }
                },

                setUserPreference(preference) {
                    this.userPreference = preference;
                    this.applyTheme();
                },

                setupSystemPreferenceListener() {
                    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                        this.systemPreference = e.matches ? 'dark' : 'light';
                        if (this.userPreference === 'system') {
                            this.applyTheme();
                        }
                    });
                },

                updateUI(isDark) {
                    const icon = document.getElementById('navbarDarkModeIcon');
                    const label = document.getElementById('navbarDarkModeLabel');
                    if (icon && label) {
                        icon.textContent = isDark ? '☀️' : '🌙';
                        label.textContent = isDark ? 'Light' : 'Dark';
                    }
                }
            };

            // Initialize theme on page load
            document.addEventListener('DOMContentLoaded', function() {
                themeManager.init();
            });

            // Global function for navbar toggle (legacy support)
            window.smartNavbarToggleDarkMode = function (button) {
                const newPreference = themeManager.userPreference === 'dark' ? 'light' : 'dark';
                themeManager.setUserPreference(newPreference);

                // Optionally sync to server if user is logged in
                if (window.authUser) {
                    fetch('{{ route('settings.preferences.update') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            preferred_locale: '{{ $authUser->preferred_locale ?? 'en' }}',
                            timezone: '{{ $authUser->timezone ?? 'UTC' }}',
                            theme_preference: newPreference,
                            receive_product_updates: {{ $authUser->receive_product_updates ? 'true' : 'false' }},
                            receive_marketing_emails: {{ $authUser->receive_marketing_emails ? 'true' : 'false' }},
                            _method: 'PATCH'
                        })
                    }).catch(console.error);
                }
            };

            // Notification Management
            const notificationManager = {
                init() {
                    this.loadNotifications();
                    this.setupEventListeners();
                },

                async loadNotifications() {
                    try {
                        const response = await fetch('{{ route('notifications.feed') }}', {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        const data = await response.json();

                        this.updateNotificationCount(data.unread_count);
                        this.renderNotifications(data.notifications);
                    } catch (error) {
                        console.error('Failed to load notifications:', error);
                    }
                },

                updateNotificationCount(count) {
                    const countElement = document.getElementById('notification-count');
                    if (count > 0) {
                        countElement.textContent = count > 99 ? '99+' : count;
                        countElement.classList.remove('hidden');
                    } else {
                        countElement.classList.add('hidden');
                    }
                },

                renderNotifications(notifications) {
                    const container = document.getElementById('notifications-list');

                    if (notifications.length === 0) {
                        container.innerHTML = `
                            <div class="text-center py-8 text-muted dark:text-muted-dark">
                                <div class="text-2xl mb-2">📭</div>
                                <p class="text-sm">{{ __app('no_notifications') }}</p>
                            </div>
                        `;
                        return;
                    }

                    container.innerHTML = notifications.map(notification => `
                        <div class="notification-item ${notification.read ? 'opacity-60' : ''} rounded-xl border border-muted/20 bg-background p-3 dark:border-muted-dark/20 dark:bg-background-dark" data-id="${notification.id}">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 mt-0.5">
                                    ${this.getNotificationIcon(notification.type)}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-foreground dark:text-foreground-dark">${notification.title}</p>
                                    <p class="text-xs text-muted dark:text-muted-dark mt-1">${notification.message}</p>
                                    <p class="text-xs text-muted dark:text-muted-dark mt-2">${this.formatDate(notification.created_at)}</p>
                                </div>
                                ${!notification.read ? `
                                    <button class="mark-read-btn flex-shrink-0 text-primary hover:text-primary/80" data-id="${notification.id}">
                                        <span class="text-xs">✓</span>
                                    </button>
                                ` : ''}
                            </div>
                        </div>
                    `).join('');

                    this.attachNotificationListeners();
                },

                getNotificationIcon(type) {
                    const icons = {
                        'project_reminders': '📋',
                        'task_deadlines': '⏰',
                        'ai_updates': '🤖',
                        'system_alerts': '⚠️'
                    };
                    return icons[type] || '🔔';
                },

                formatDate(dateString) {
                    const date = new Date(dateString);
                    const now = new Date();
                    const diffInHours = Math.floor((now - date) / (1000 * 60 * 60));

                    if (diffInHours < 1) return 'Just now';
                    if (diffInHours < 24) return `${diffInHours}h ago`;
                    if (diffInHours < 168) return `${Math.floor(diffInHours / 24)}d ago`;
                    return date.toLocaleDateString();
                },

                attachNotificationListeners() {
                    // Mark individual notification as read
                    document.querySelectorAll('.mark-read-btn').forEach(btn => {
                        btn.addEventListener('click', async (e) => {
                            e.stopPropagation();
                            const notificationId = e.currentTarget.dataset.id;
                            await this.markAsRead(notificationId);
                        });
                    });

                    // Mark notification as read when clicked
                    document.querySelectorAll('.notification-item').forEach(item => {
                        item.addEventListener('click', async () => {
                            const notificationId = item.dataset.id;
                            if (!item.classList.contains('opacity-60')) {
                                await this.markAsRead(notificationId);
                            }
                        });
                    });
                },

                async markAsRead(notificationId) {
                    try {
                        const response = await fetch(`{{ url('/notifications') }}/${notificationId}/read`, {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });

                        if (response.ok) {
                            this.loadNotifications(); // Refresh the list
                        }
                    } catch (error) {
                        console.error('Failed to mark notification as read:', error);
                    }
                },

                setupEventListeners() {
                    // Mark all as read
                    const markAllBtn = document.getElementById('mark-all-read');
                    if (markAllBtn) {
                        markAllBtn.addEventListener('click', async () => {
                            try {
                                const response = await fetch('{{ route('notifications.mark-all-read') }}', {
                                    method: 'PATCH',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                    }
                                });

                                if (response.ok) {
                                    this.loadNotifications();
                                }
                            } catch (error) {
                                console.error('Failed to mark all notifications as read:', error);
                            }
                        });
                    }
                }
            };

            // Initialize notifications on page load
            document.addEventListener('DOMContentLoaded', function() {
                themeManager.init();
                notificationManager.init();
            });
        </script>
    @endonce
</div>
