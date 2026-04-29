{{-- Floating Action Button for Quick Create --}}
<div x-data="fabManager()" x-cloak>
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-75"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-75"
        class="fixed bottom-6 right-6 z-40 flex flex-col-reverse gap-2"
    >
        <template x-for="action in actions" :key="action.id">
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-4"
                :style="`transition-delay: ${action.delay}ms`"
                class="flex items-center gap-3"
            >
                <span
                    x-text="action.label"
                    class="whitespace-nowrap rounded-lg bg-background px-3 py-1.5 text-sm font-medium text-foreground shadow-lg dark:bg-background-dark dark:text-foreground-dark"
                ></span>
                <a
                    :href="action.href"
                    class="flex h-12 w-12 items-center justify-center rounded-2xl border border-muted/20 bg-background shadow-lg transition hover:scale-110 hover:bg-primary hover:text-primary-foreground dark:border-muted-dark/20 dark:bg-background-dark dark:hover:bg-primary dark:hover:text-primary-foreground"
                    :title="action.label"
                >
                    <span x-text="action.icon" class="text-xl"></span>
                </a>
            </div>
        </template>
    </div>

    <button
        x-on:click="toggle"
        class="fixed bottom-6 right-6 z-50 flex h-16 w-16 items-center justify-center rounded-full bg-primary text-primary-foreground shadow-lg transition-all duration-300 hover:scale-110 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-primary/30"
        :class="open ? 'rotate-45' : ''"
        title="Quick Actions"
    >
        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
    </button>
</div>

<script>
function fabManager() {
    return {
        open: false,
        
        actions: [
            {
                id: 'new-project',
                label: 'New Project',
                icon: '📁',
                href: '{{ route("projects.create") }}',
                delay: 0,
            },
            {
                id: 'new-task',
                label: 'New Task',
                icon: '✅',
                href: '{{ route("tasks.create") }}',
                delay: 50,
            },
            {
                id: 'ai-chat',
                label: 'AI Chat',
                icon: '🤖',
                href: '{{ route("chat") }}',
                delay: 100,
            },
            {
                id: 'builder',
                label: 'Smart Builder',
                icon: '🔨',
                href: '{{ route("builder") }}',
                delay: 150,
            },
        ],
        
        toggle() {
            this.open = !this.open;
        },
        
        init() {
            // Close on escape
            window.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.open) {
                    this.open = false;
                }
            });
            
            // Close when clicking outside
            document.addEventListener('click', (e) => {
                if (this.open && !e.target.closest('[x-data="fabManager()"]')) {
                    this.open = false;
                }
            });
        }
    }
}
</script>
