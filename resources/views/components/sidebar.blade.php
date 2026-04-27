<div
    x-data="{ collapsed: false, mobileOpen: false }"
    class="relative flex h-full flex-col rounded-2xl border border-muted/20 bg-background p-5 shadow-[var(--shadow-premium)] backdrop-blur-xl transition-all duration-500 ease-[cubic-bezier(0.4,0,0.2,1)] dark:border-muted-dark/20 dark:bg-background-dark"
    :class="{ 'w-20': collapsed && !mobileOpen, 'w-72': !collapsed || mobileOpen, 'fixed inset-y-0 left-0 z-50 w-72': mobileOpen, 'hidden lg:flex': !mobileOpen }"
    @resize.window="if (window.innerWidth >= 1024) mobileOpen = false"
    @sidebar-state.window="mobileOpen = $event.detail"
>
    <!-- Mobile overlay backdrop -->
    <div
        x-show="mobileOpen"
        x-transition.opacity.duration.200ms
        class="fixed inset-0 z-40 bg-black/50 lg:hidden"
        @click="mobileOpen = false"
    ></div>

    <div class="flex items-center justify-between gap-3 pb-5">
        <div x-show="!collapsed || mobileOpen" class="transition-opacity duration-200">
            <p class="text-sm uppercase tracking-[.24em] text-secondary">Workspace</p>
            <x-brand-logo class="mt-2" badge-size="h-8 w-8" badge-text-size="text-[10px]" name-size="text-lg" :show-subtitle="false" />
        </div>
        <!-- Desktop collapse toggle (hidden on mobile) -->
        <button
            @click="collapsed = !collapsed"
            class="touch-target hidden lg:inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-muted/20 bg-background-secondary text-foreground transition hover:bg-muted/10 dark:border-muted-dark/20 dark:bg-background-secondary-dark dark:text-foreground-dark dark:hover:bg-muted-dark/10"
            :aria-label="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
        >
            <span x-show="!collapsed">◀</span>
            <span x-show="collapsed">▶</span>
        </button>
        <!-- Mobile close button -->
        <button
            @click="mobileOpen = false"
            class="touch-target lg:hidden inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-muted/20 bg-background-secondary text-foreground transition hover:bg-muted/10 dark:border-muted-dark/20 dark:bg-background-secondary-dark dark:text-foreground-dark dark:hover:bg-muted-dark/10"
            aria-label="Close sidebar"
        >
            ✕
        </button>
    </div>

    <nav class="flex-1 space-y-1">
        <a
            href="{{ route('dashboard') }}"
            class="group flex min-h-[44px] items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-foreground transition-all duration-200 ease-out hover:bg-muted/10 hover:shadow-soft hover:translate-x-1 dark:text-foreground-dark dark:hover:bg-muted-dark/10"
            :class="{ 'bg-primary/10 text-primary border border-primary/20': request()->routeIs('dashboard') }"
        >
            <span class="text-lg transition-transform duration-300 group-hover:scale-110">🏠</span>
            <span x-show="!collapsed || mobileOpen" class="transition-opacity duration-200">Dashboard</span>
        </a>

        <a
            href="{{ route('projects.index') }}"
            class="group flex min-h-[44px] items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-foreground transition-all duration-300 ease-out hover:bg-muted/10 hover:shadow-soft hover:translate-x-1 dark:text-foreground-dark dark:hover:bg-muted-dark/10"
            :class="{ 'bg-primary/10 text-primary border border-primary/20': request()->routeIs('projects.*') }"
        >
            <span class="text-lg transition-transform duration-300 group-hover:scale-110">📁</span>
            <span x-show="!collapsed || mobileOpen" class="transition-opacity duration-200">Projects</span>
        </a>

        <a
            href="{{ route('chat') }}"
            class="group flex min-h-[44px] items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-foreground transition-all duration-300 ease-out hover:bg-muted/10 hover:shadow-soft hover:translate-x-1 dark:text-foreground-dark dark:hover:bg-muted-dark/10"
            :class="{ 'bg-primary/10 text-primary border border-primary/20': request()->routeIs('chat') }"
        >
            <span class="text-lg transition-transform duration-300 group-hover:scale-110">🤖</span>
            <span x-show="!collapsed || mobileOpen" class="transition-opacity duration-200">AI Chat</span>
        </a>

        <a
            href="{{ route('builder') }}"
            class="group flex min-h-[44px] items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-foreground transition-all duration-300 ease-out hover:bg-muted/10 hover:shadow-soft hover:translate-x-1 dark:text-foreground-dark dark:hover:bg-muted-dark/10"
            :class="{ 'bg-primary/10 text-primary border border-primary/20': request()->routeIs('builder') }"
        >
            <span class="text-lg transition-transform duration-300 group-hover:scale-110">🔧</span>
            <span x-show="!collapsed || mobileOpen" class="transition-opacity duration-200">Smart Builder</span>
        </a>

        <a
            href="{{ route('tasks.index') }}"
            class="group flex min-h-[44px] items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-foreground transition-all duration-300 ease-out hover:bg-muted/10 hover:shadow-soft hover:translate-x-1 dark:text-foreground-dark dark:hover:bg-muted-dark/10"
            :class="{ 'bg-primary/10 text-primary border border-primary/20': request()->routeIs('tasks.*') }"
        >
            <span class="text-lg transition-transform duration-300 group-hover:scale-110">✅</span>
            <span x-show="!collapsed || mobileOpen" class="transition-opacity duration-200">Tasks</span>
        </a>

        <a
            href="{{ route('budgets.index') }}"
            class="group flex min-h-[44px] items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-foreground transition-all duration-300 ease-out hover:bg-muted/10 hover:shadow-soft hover:translate-x-1 dark:text-foreground-dark dark:hover:bg-muted-dark/10"
            :class="{ 'bg-primary/10 text-primary border border-primary/20': request()->routeIs('budgets.*') }"
        >
            <span class="text-lg transition-transform duration-300 group-hover:scale-110">💰</span>
            <span x-show="!collapsed || mobileOpen" class="transition-opacity duration-200">Budgets</span>
        </a>

        <a
            href="{{ route('notifications.index') }}"
            class="group flex min-h-[44px] items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-foreground transition-all duration-300 ease-out hover:bg-muted/10 hover:shadow-soft hover:translate-x-1 dark:text-foreground-dark dark:hover:bg-muted-dark/10"
            :class="{ 'bg-primary/10 text-primary border border-primary/20': request()->routeIs('notifications.*') }"
        >
            <span class="text-lg transition-transform duration-300 group-hover:scale-110">🔔</span>
            <span x-show="!collapsed || mobileOpen" class="transition-opacity duration-200">Notifications</span>
        </a>

        <a
            href="{{ route('resources') }}"
            class="group flex min-h-[44px] items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-foreground transition-all duration-300 ease-out hover:bg-muted/10 hover:shadow-soft hover:translate-x-1 dark:text-foreground-dark dark:hover:bg-muted-dark/10"
            :class="{ 'bg-primary/10 text-primary border border-primary/20': request()->routeIs('resources') }"
        >
            <span class="text-lg transition-transform duration-300 group-hover:scale-110">📚</span>
            <span x-show="!collapsed || mobileOpen" class="transition-opacity duration-200">Resources</span>
        </a>

        <a
            href="{{ route('web3.profile') }}"
            class="group flex min-h-[44px] items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-foreground transition-all duration-300 ease-out hover:bg-muted/10 hover:shadow-soft hover:translate-x-1 dark:text-foreground-dark dark:hover:bg-muted-dark/10"
            :class="{ 'bg-primary/10 text-primary border border-primary/20': request()->routeIs('web3.*') }"
        >
            <span class="text-lg transition-transform duration-300 group-hover:scale-110">🔗</span>
            <span x-show="!collapsed || mobileOpen" class="transition-opacity duration-200">Web3 Profile</span>
        </a>

        <a
            href="{{ route('settings') }}"
            class="group flex min-h-[44px] items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-foreground transition-all duration-300 ease-out hover:bg-muted/10 hover:shadow-soft hover:translate-x-1 dark:text-foreground-dark dark:hover:bg-muted-dark/10"
            :class="{ 'bg-primary/10 text-primary border border-primary/20': request()->routeIs('settings') }"
        >
            <span class="text-lg transition-transform duration-300 group-hover:scale-110">⚙️</span>
            <span x-show="!collapsed || mobileOpen" class="transition-opacity duration-200">Settings</span>
        </a>
        @if(auth()->user()?->isAdmin())
            <a
                href="{{ route('admin.index') }}"
                class="group flex min-h-[44px] items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-foreground transition-all duration-300 ease-out hover:bg-muted/10 hover:shadow-soft hover:translate-x-1 dark:text-foreground-dark dark:hover:bg-muted-dark/10"
                :class="{ 'bg-primary/10 text-primary border border-primary/20': request()->routeIs('admin.*') }"
            >
                <span class="text-lg transition-transform duration-300 group-hover:scale-110">🛡️</span>
                <span x-show="!collapsed || mobileOpen" class="transition-opacity duration-200">Admin</span>
            </a>
        @endif
    </nav>

    <div x-show="!collapsed || mobileOpen" class="mt-8 rounded-2xl bg-gradient-to-br from-primary to-secondary p-5 text-primary-foreground shadow-card transition-opacity duration-200">
        <p class="text-xs uppercase tracking-[.28em] text-secondary-foreground">Need help?</p>
        <p class="mt-3 text-sm leading-relaxed text-primary-foreground/90">Visit the help center, or reach out to our support team for assistance.</p>
        <a href="#" class="mt-4 inline-flex min-h-[44px] items-center rounded-full bg-primary-foreground px-4 py-3 text-sm font-semibold text-primary transition hover:bg-primary-foreground/90">Get support</a>
    </div>
</div>
