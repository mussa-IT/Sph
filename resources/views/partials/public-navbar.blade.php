<nav class="fixed inset-x-0 top-0 z-50 transition-all duration-500"
     :class="scrolled ? 'bg-background/80 backdrop-blur-2xl border-b border-muted/10 shadow-[0_1px_40px_-12px_rgba(0,0,0,0.12)] dark:bg-background-dark/80 dark:border-muted-dark/10' : 'bg-transparent'">
    <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-6 lg:px-8">
        <!-- Logo -->
        <a href="{{ route('home') }}" class="group">
            <x-brand-logo
                class="transition-transform duration-300 group-hover:scale-105"
                badge-size="h-10 w-10"
                badge-text-size="text-sm"
                name-size="text-lg"
                :show-subtitle="false"
            />
        </a>

        <!-- Desktop Links -->
        <div class="hidden md:flex items-center gap-8">
            <a href="#features" class="text-sm font-medium text-muted/80 transition-colors hover:text-foreground dark:text-muted-dark/80 dark:hover:text-foreground-dark">Features</a>
            <a href="#pricing" class="text-sm font-medium text-muted/80 transition-colors hover:text-foreground dark:text-muted-dark/80 dark:hover:text-foreground-dark">Pricing</a>
            <a href="{{ route('login') }}" class="text-sm font-medium text-muted/80 transition-colors hover:text-foreground dark:text-muted-dark/80 dark:hover:text-foreground-dark">Log in</a>
            <a href="{{ route('register') }}" class="btn-brand interactive-lift">
                Get Started
            </a>
        </div>

        <!-- Mobile Menu Button -->
        <button
            @click="mobileMenuOpen = !mobileMenuOpen"
            class="md:hidden inline-flex h-10 w-10 items-center justify-center rounded-xl border border-muted/20 bg-background-secondary text-foreground transition-all duration-200 hover:bg-muted/10 hover:scale-105 active:scale-95 dark:border-muted-dark/20 dark:bg-background-secondary-dark dark:text-foreground-dark dark:hover:bg-muted-dark/10"
            aria-label="Toggle menu"
        >
            <svg x-show="!mobileMenuOpen" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            <svg x-show="mobileMenuOpen" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <!-- Mobile Menu -->
    <div
        x-show="mobileMenuOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="md:hidden border-t border-muted/10 bg-background/95 backdrop-blur-xl dark:border-muted-dark/10 dark:bg-background-dark/95"
        @click.away="mobileMenuOpen = false"
    >
        <div class="space-y-1 px-6 py-4">
            <a href="#features" @click="mobileMenuOpen = false" class="block rounded-xl px-4 py-3 text-sm font-medium text-muted transition hover:bg-muted/10 hover:text-foreground dark:text-muted-dark dark:hover:bg-muted-dark/10 dark:hover:text-foreground-dark">Features</a>
            <a href="#pricing" @click="mobileMenuOpen = false" class="block rounded-xl px-4 py-3 text-sm font-medium text-muted transition hover:bg-muted/10 hover:text-foreground dark:text-muted-dark dark:hover:bg-muted-dark/10 dark:hover:text-foreground-dark">Pricing</a>
            <a href="{{ route('login') }}" class="block rounded-xl px-4 py-3 text-sm font-medium text-muted transition hover:bg-muted/10 hover:text-foreground dark:text-muted-dark dark:hover:bg-muted-dark/10 dark:hover:text-foreground-dark">Log in</a>
            <a href="{{ route('register') }}" class="btn-brand interactive-lift w-full">Get Started</a>
        </div>
    </div>
</nav>
