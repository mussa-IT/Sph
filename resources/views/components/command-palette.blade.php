{{-- Premium Command Palette - Spotlight-style quick navigation --}}
<div x-data="commandPalette()" x-on:keydown.window="handleKeydown($event)" x-cloak>
    {{-- Command Palette Overlay --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm"
        x-on:click="open = false"
    ></div>

    {{-- Command Palette Modal --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
        class="fixed inset-0 z-50 flex items-start justify-center pt-[20vh] p-4"
        x-on:click.self="open = false"
    >
        <div class="w-full max-w-2xl overflow-hidden rounded-2xl border border-muted/20 bg-background shadow-2xl dark:border-muted-dark/20 dark:bg-background-dark">
            {{-- Search Input --}}
            <div class="flex items-center gap-3 border-b border-muted/10 px-4 py-4 dark:border-muted-dark/10">
                <svg class="h-5 w-5 text-muted dark:text-muted-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input
                    x-ref="input"
                    x-model="search"
                    x-on:keydown.down.prevent="moveDown()"
                    x-on:keydown.up.prevent="moveUp()"
                    x-on:keydown.enter.prevent="select()"
                    x-on:keydown.escape.prevent="open = false"
                    type="text"
                    placeholder="Search commands, projects, or navigate..."
                    class="flex-1 bg-transparent text-lg text-foreground placeholder:text-muted/50 focus:outline-none dark:text-foreground-dark dark:placeholder:text-muted-dark/50"
                >
                <kbd class="hidden rounded-lg border border-muted/30 bg-muted/10 px-2 py-1 text-xs font-medium text-muted dark:border-muted-dark/30 dark:bg-muted-dark/10 dark:text-muted-dark sm:block">ESC</kbd>
            </div>

            {{-- Results --}}
            <div class="max-h-[50vh] overflow-y-auto py-2" x-ref="results">
                {{-- Quick Actions Section --}}
                <div x-show="!search" class="px-2">
                    <p class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-muted dark:text-muted-dark">Quick Actions</p>
                    <template x-for="(item, index) in quickActions" :key="item.id">
                        <button
                            x-on:click="execute(item)"
                            x-on:mouseenter="activeIndex = index"
                            x-bind:class="activeIndex === index ? 'bg-primary/10 text-primary dark:bg-primary/20' : 'text-foreground hover:bg-muted/5 dark:text-foreground-dark dark:hover:bg-muted-dark/5'"
                            class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left transition-colors"
                        >
                            <span x-html="item.icon" class="flex h-8 w-8 items-center justify-center rounded-lg bg-muted/10 dark:bg-muted-dark/10"></span>
                            <div class="flex-1">
                                <p x-text="item.title" class="font-medium"></p>
                                <p x-text="item.description" class="text-sm text-muted dark:text-muted-dark"></p>
                            </div>
                            <kbd x-show="item.shortcut" x-text="item.shortcut" class="rounded border border-muted/30 bg-muted/10 px-1.5 py-0.5 text-xs text-muted dark:border-muted-dark/30 dark:bg-muted-dark/10 dark:text-muted-dark"></kbd>
                        </button>
                    </template>
                </div>

                {{-- Search Results --}}
                <div x-show="search && filteredItems.length > 0" class="px-2">
                    <p class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-muted dark:text-muted-dark">
                        Results (<span x-text="filteredItems.length"></span>)
                    </p>
                    <template x-for="(item, index) in filteredItems" :key="item.id">
                        <button
                            x-on:click="execute(item)"
                            x-on:mouseenter="activeIndex = index + (quickActions.length || 0)"
                            x-bind:class="activeIndex === (index + (quickActions.length || 0)) ? 'bg-primary/10 text-primary dark:bg-primary/20' : 'text-foreground hover:bg-muted/5 dark:text-foreground-dark dark:hover:bg-muted-dark/5'"
                            class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left transition-colors"
                        >
                            <span x-html="item.icon" class="flex h-8 w-8 items-center justify-center rounded-lg bg-muted/10 text-lg dark:bg-muted-dark/10"></span>
                            <div class="flex-1">
                                <p x-html="highlight(item.title)" class="font-medium"></p>
                                <p x-text="item.description" class="text-sm text-muted dark:text-muted-dark"></p>
                            </div>
                            <span x-text="item.category" class="text-xs text-muted/60 dark:text-muted-dark/60"></span>
                        </button>
                    </template>
                </div>

                {{-- Empty State --}}
                <div x-show="search && filteredItems.length === 0" class="px-4 py-8 text-center">
                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-muted/10 dark:bg-muted-dark/10">
                        <svg class="h-6 w-6 text-muted dark:text-muted-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-muted dark:text-muted-dark">No results found for "<span x-text="search" class="font-medium"></span>"</p>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-between border-t border-muted/10 bg-muted/5 px-4 py-2 dark:border-muted-dark/10 dark:bg-muted-dark/5">
                <div class="flex items-center gap-4 text-xs text-muted dark:text-muted-dark">
                    <span class="flex items-center gap-1"><kbd class="rounded border border-muted/30 px-1">↑</kbd> <kbd class="rounded border border-muted/30 px-1">↓</kbd> to navigate</span>
                    <span class="flex items-center gap-1"><kbd class="rounded border border-muted/30 px-1">↵</kbd> to select</span>
                </div>
                <span class="text-xs text-muted dark:text-muted-dark">Press <kbd class="rounded border border-muted/30 px-1">ESC</kbd> to close</span>
            </div>
        </div>
    </div>

    {{-- Floating Trigger Button --}}
    <button
        x-show="!open"
        x-on:click="open = true; $nextTick(() => $refs.input.focus())"
        class="fixed bottom-6 right-6 z-40 flex items-center gap-2 rounded-full bg-primary px-4 py-3 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/25 transition-all hover:scale-105 hover:shadow-xl sm:bottom-8 sm:right-8"
        title="Open Command Palette (Cmd/Ctrl + K)"
    >
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <span class="hidden sm:inline">Search</span>
        <kbd class="ml-2 hidden rounded bg-white/20 px-1.5 py-0.5 text-xs sm:inline">⌘K</kbd>
    </button>
</div>

<script>
function commandPalette() {
    return {
        open: false,
        search: '',
        activeIndex: 0,
        
        items: [
            // Navigation
            { id: 'dash', title: 'Dashboard', description: 'Go to your dashboard', category: 'Navigation', icon: '📊', url: '{{ route("dashboard") }}' },
            { id: 'projects', title: 'Projects', description: 'View all projects', category: 'Navigation', icon: '📁', url: '{{ route("projects.index") }}' },
            { id: 'tasks', title: 'Tasks', description: 'Manage your tasks', category: 'Navigation', icon: '✅', url: '{{ route("tasks.index") }}' },
            { id: 'chat', title: 'AI Chat', description: 'Open AI assistant', category: 'Navigation', icon: '🤖', url: '{{ route("chat") }}' },
            { id: 'builder', title: 'Project Builder', description: 'Build projects with AI', category: 'Navigation', icon: '🛠️', url: '{{ route("builder") }}' },
            { id: 'settings', title: 'Settings', description: 'Account settings', category: 'Navigation', icon: '⚙️', url: '{{ route("settings") }}' },
            
            // Actions
            { id: 'new-project', title: 'Create New Project', description: 'Start a new project', category: 'Action', icon: '➕', url: '{{ route("projects.create") }}' },
            { id: 'new-task', title: 'Create Task', description: 'Add a new task', category: 'Action', icon: '📝', url: '{{ route("tasks.create") }}' },
            { id: 'profile', title: 'My Profile', description: 'View your profile', category: 'Navigation', icon: '👤', url: '{{ route("profile") }}' },
            { id: 'resources', title: 'Resources', description: 'Access resources', category: 'Navigation', icon: '📚', url: '{{ route("resources") }}' },
            
            // Admin (only for admins)
            @if(auth()->user()?->isAdmin())
            { id: 'admin', title: 'Admin Dashboard', description: 'Platform administration', category: 'Admin', icon: '🔐', url: '{{ route("admin.index") }}' },
            { id: 'admin-users', title: 'User Management', description: 'Manage platform users', category: 'Admin', icon: '👥', url: '{{ route("admin.users.index") }}' },
            @endif
            
            // Preferences
            { id: 'toggle-theme', title: 'Toggle Theme', description: 'Switch light/dark mode', category: 'Preferences', icon: '🌓', action: 'toggleTheme' },
            { id: 'logout', title: 'Logout', description: 'Sign out of your account', category: 'Account', icon: '🚪', url: '{{ route("logout") }}', method: 'post' },
        ],
        
        get quickActions() {
            return this.items.filter(i => 
                ['dash', 'new-project', 'chat', 'builder'].includes(i.id)
            );
        },
        
        get filteredItems() {
            if (!this.search) return [];
            const search = this.search.toLowerCase();
            return this.items.filter(item => 
                item.title.toLowerCase().includes(search) ||
                item.description.toLowerCase().includes(search) ||
                item.category.toLowerCase().includes(search)
            );
        },
        
        get allItems() {
            return this.search ? this.filteredItems : this.quickActions;
        },
        
        handleKeydown(event) {
            // Cmd/Ctrl + K to open
            if ((event.metaKey || event.ctrlKey) && event.key === 'k') {
                event.preventDefault();
                this.open = true;
                this.$nextTick(() => this.$refs.input.focus());
            }
            // Cmd/Ctrl + / for help
            if ((event.metaKey || event.ctrlKey) && event.key === '/') {
                event.preventDefault();
                // Could show shortcuts modal
            }
        },
        
        moveDown() {
            this.activeIndex = Math.min(this.activeIndex + 1, this.allItems.length - 1);
            this.scrollToActive();
        },
        
        moveUp() {
            this.activeIndex = Math.max(this.activeIndex - 1, 0);
            this.scrollToActive();
        },
        
        scrollToActive() {
            this.$nextTick(() => {
                const active = this.$refs.results.querySelector('.bg-primary\\/10, .bg-primary\\/20');
                if (active) active.scrollIntoView({ block: 'nearest' });
            });
        },
        
        select() {
            const item = this.allItems[this.activeIndex];
            if (item) this.execute(item);
        },
        
        execute(item) {
            if (item.action === 'toggleTheme') {
                document.documentElement.classList.toggle('dark');
                localStorage.setItem('darkMode', document.documentElement.classList.contains('dark'));
                this.open = false;
                return;
            }
            
            if (item.method === 'post') {
                // Create form for POST request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = item.url;
                form.innerHTML = '@csrf';
                document.body.appendChild(form);
                form.submit();
            } else {
                window.location.href = item.url;
            }
        },
        
        highlight(text) {
            if (!this.search) return text;
            const regex = new RegExp(`(${this.search})`, 'gi');
            return text.replace(regex, '<span class="bg-primary/20 text-primary dark:bg-primary/30">$1</span>');
        },
        
        init() {
            this.$watch('search', () => this.activeIndex = 0);
        }
    }
}
</script>
