{{-- Keyboard Shortcuts Help Modal --}}
<div x-data="keyboardShortcuts()" x-cloak>
    {{-- Shortcut Modal --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-on:keydown.escape.window="open = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4"
        x-on:click.self="open = false"
    >
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="w-full max-w-lg overflow-hidden rounded-2xl border border-muted/20 bg-background shadow-2xl dark:border-muted-dark/20 dark:bg-background-dark"
        >
            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-muted/10 px-6 py-4 dark:border-muted-dark/10">
                <h3 class="text-lg font-semibold text-foreground dark:text-foreground-dark">Keyboard Shortcuts</h3>
                <button x-on:click="open = false" class="rounded-lg p-1 text-muted hover:bg-muted/10 hover:text-foreground dark:text-muted-dark dark:hover:bg-muted-dark/10 dark:hover:text-foreground-dark">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Shortcuts List --}}
            <div class="max-h-[60vh] overflow-y-auto p-6">
                <template x-for="(section, sectionIndex) in sections" :key="sectionIndex">
                    <div class="mb-6 last:mb-0">
                        <h4 x-text="section.title" class="mb-3 text-xs font-semibold uppercase tracking-wider text-muted dark:text-muted-dark"></h4>
                        <div class="space-y-2">
                            <template x-for="shortcut in section.shortcuts" :key="shortcut.id">
                                <div class="flex items-center justify-between rounded-xl bg-muted/5 px-4 py-3 dark:bg-muted-dark/5">
                                    <span x-text="shortcut.action" class="text-sm text-foreground dark:text-foreground-dark"></span>
                                    <div class="flex items-center gap-1">
                                        <template x-for="(key, keyIndex) in shortcut.keys" :key="keyIndex">
                                            <span class="flex items-center gap-1">
                                                <kbd x-text="key" class="rounded-lg border border-muted/30 bg-background px-2 py-1 text-xs font-mono font-medium text-foreground shadow-sm dark:border-muted-dark/30 dark:bg-background-dark dark:text-foreground-dark"></kbd>
                                                <span x-show="keyIndex < shortcut.keys.length - 1" class="text-muted dark:text-muted-dark">+</span>
                                            </span>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Footer --}}
            <div class="border-t border-muted/10 bg-muted/5 px-6 py-3 dark:border-muted-dark/10 dark:bg-muted-dark/5">
                <p class="text-center text-xs text-muted dark:text-muted-dark">
                    Press <kbd class="rounded border border-muted/30 px-1">?</kbd> anytime to show this help
                </p>
            </div>
        </div>
    </div>
</div>

<script>
function keyboardShortcuts() {
    return {
        open: false,
        
        sections: [
            {
                title: 'General',
                shortcuts: [
                    { id: 'search', action: 'Open Command Palette', keys: ['⌘', 'K'] },
                    { id: 'help', action: 'Show Keyboard Shortcuts', keys: ['?'] },
                    { id: 'theme', action: 'Toggle Dark/Light Mode', keys: ['⌘', 'D'] },
                    { id: 'focus', action: 'Focus Search', keys: ['/'] },
                ]
            },
            {
                title: 'Navigation',
                shortcuts: [
                    { id: 'dashboard', action: 'Go to Dashboard', keys: ['G', 'D'] },
                    { id: 'projects', action: 'Go to Projects', keys: ['G', 'P'] },
                    { id: 'tasks', action: 'Go to Tasks', keys: ['G', 'T'] },
                    { id: 'chat', action: 'Open AI Chat', keys: ['G', 'C'] },
                    { id: 'builder', action: 'Open Builder', keys: ['G', 'B'] },
                    { id: 'settings', action: 'Go to Settings', keys: ['G', 'S'] },
                ]
            },
            {
                title: 'Actions',
                shortcuts: [
                    { id: 'new-project', action: 'Create New Project', keys: ['N', 'P'] },
                    { id: 'new-task', action: 'Create New Task', keys: ['N', 'T'] },
                    { id: 'save', action: 'Save Form', keys: ['⌘', 'S'] },
                    { id: 'cancel', action: 'Cancel/Close', keys: ['Esc'] },
                ]
            },
        ],
        
        init() {
            // Listen for ? key to open help
            window.addEventListener('keydown', (e) => {
                // Don't trigger if in input
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.isContentEditable) return;
                
                if (e.key === '?' && !e.metaKey && !e.ctrlKey && !e.altKey) {
                    e.preventDefault();
                    this.open = true;
                }
            });
        }
    }
}

// Global keyboard shortcuts handler
document.addEventListener('alpine:init', () => {
    window.addEventListener('keydown', (e) => {
        // Skip if in input field
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.isContentEditable) return;
        
        const key = e.key.toLowerCase();
        const mod = e.metaKey || e.ctrlKey;
        
        // Command Palette: Cmd/Ctrl + K
        if (mod && key === 'k') {
            e.preventDefault();
            window.dispatchEvent(new CustomEvent('open-command-palette'));
            return;
        }
        
        // Toggle Theme: Cmd/Ctrl + D
        if (mod && key === 'd') {
            e.preventDefault();
            document.documentElement.classList.toggle('dark');
            localStorage.setItem('darkMode', document.documentElement.classList.contains('dark'));
            return;
        }
        
        // G-key navigation (vim-style)
        if (key === 'g' && !mod) {
            window._gKeyPressed = Date.now();
            return;
        }
        
        // Check for G + key combinations
        if (window._gKeyPressed && Date.now() - window._gKeyPressed < 1000) {
            window._gKeyPressed = null;
            
            const routes = {
                'd': '{{ route("dashboard") }}',
                'p': '{{ route("projects.index") }}',
                't': '{{ route("tasks.index") }}',
                'c': '{{ route("chat") }}',
                'b': '{{ route("builder") }}',
                's': '{{ route("settings") }}',
            };
            
            if (routes[key]) {
                e.preventDefault();
                window.location.href = routes[key];
                return;
            }
        }
        
        // N-key actions (new item)
        if (key === 'n' && !mod) {
            window._nKeyPressed = Date.now();
            return;
        }
        
        if (window._nKeyPressed && Date.now() - window._nKeyPressed < 1000) {
            window._nKeyPressed = null;
            
            const newRoutes = {
                'p': '{{ route("projects.create") }}',
                't': '{{ route("tasks.create") }}',
            };
            
            if (newRoutes[key]) {
                e.preventDefault();
                window.location.href = newRoutes[key];
                return;
            }
        }
        
        // Slash for search focus
        if (key === '/' && !mod) {
            const searchInput = document.querySelector('input[type="search"], input[placeholder*="Search"]');
            if (searchInput) {
                e.preventDefault();
                searchInput.focus();
            }
            return;
        }
    });
});
</script>
