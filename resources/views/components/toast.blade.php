{{-- Toast Notification Component --}}
<div x-data="toastManager()" x-cloak>
    <div
        x-show="toasts.length > 0"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
        class="fixed bottom-4 right-4 z-50 flex flex-col gap-2"
    >
        <template x-for="toast in toasts" :key="toast.id">
            <div
                x-show="toast.visible"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-x-4"
                x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-x-0"
                x-transition:leave-end="opacity-0 translate-x-4"
                :class="[
                    'min-w-[320px] max-w-md rounded-2xl border shadow-lg p-4',
                    toast.type === 'success' ? 'border-emerald-500/20 bg-emerald-500/10 dark:border-emerald-400/20 dark:bg-emerald-400/10' : '',
                    toast.type === 'error' ? 'border-rose-500/20 bg-rose-500/10 dark:border-rose-400/20 dark:bg-rose-400/10' : '',
                    toast.type === 'warning' ? 'border-amber-500/20 bg-amber-500/10 dark:border-amber-400/20 dark:bg-amber-400/10' : '',
                    toast.type === 'info' ? 'border-blue-500/20 bg-blue-500/10 dark:border-blue-400/20 dark:bg-blue-400/10' : '',
                ]"
            >
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <span x-show="toast.type === 'success'" class="text-emerald-600 dark:text-emerald-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </span>
                        <span x-show="toast.type === 'error'" class="text-rose-600 dark:text-rose-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </span>
                        <span x-show="toast.type === 'warning'" class="text-amber-600 dark:text-amber-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </span>
                        <span x-show="toast.type === 'info'" class="text-blue-600 dark:text-blue-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p
                            :class="[
                                'text-sm font-medium',
                                toast.type === 'success' ? 'text-emerald-900 dark:text-emerald-100' : '',
                                toast.type === 'error' ? 'text-rose-900 dark:text-rose-100' : '',
                                toast.type === 'warning' ? 'text-amber-900 dark:text-amber-100' : '',
                                toast.type === 'info' ? 'text-blue-900 dark:text-blue-100' : '',
                            ]"
                            x-text="toast.title"
                        ></p>
                        <p
                            :class="[
                                'mt-1 text-sm',
                                toast.type === 'success' ? 'text-emerald-700 dark:text-emerald-200' : '',
                                toast.type === 'error' ? 'text-rose-700 dark:text-rose-200' : '',
                                toast.type === 'warning' ? 'text-amber-700 dark:text-amber-200' : '',
                                toast.type === 'info' ? 'text-blue-700 dark:text-blue-200' : '',
                            ]"
                            x-text="toast.message"
                        ></p>
                    </div>
                    <button
                        x-on:click="dismiss(toast.id)"
                        class="flex-shrink-0 rounded-lg p-1 transition hover:bg-black/5 dark:hover:bg-white/10"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                @if($undoable ?? false)
                <div class="mt-3 flex items-center justify-end gap-2">
                    <button
                        x-on:click="undo(toast.id)"
                        class="text-sm font-medium underline hover:no-underline"
                        :class="[
                            toast.type === 'success' ? 'text-emerald-700 dark:text-emerald-200' : '',
                            toast.type === 'error' ? 'text-rose-700 dark:text-rose-200' : '',
                            toast.type === 'warning' ? 'text-amber-700 dark:text-amber-200' : '',
                            toast.type === 'info' ? 'text-blue-700 dark:text-blue-200' : '',
                        ]"
                    >
                        Undo
                    </button>
                </div>
                @endif
            </div>
        </template>
    </div>
</div>

<script>
function toastManager() {
    return {
        toasts: [],
        toastId: 0,

        show(type, title, message, options = {}) {
            const id = ++this.toastId;
            const toast = {
                id,
                type,
                title,
                message,
                visible: true,
                duration: options.duration ?? 5000,
                undo: options.undo ?? null,
            };

            this.toasts.push(toast);

            if (toast.duration > 0) {
                setTimeout(() => this.dismiss(id), toast.duration);
            }

            return id;
        },

        success(title, message, options) {
            return this.show('success', title, message, options);
        },

        error(title, message, options) {
            return this.show('error', title, message, options);
        },

        warning(title, message, options) {
            return this.show('warning', title, message, options);
        },

        info(title, message, options) {
            return this.show('info', title, message, options);
        },

        dismiss(id) {
            const toast = this.toasts.find(t => t.id === id);
            if (toast) {
                toast.visible = false;
                setTimeout(() => {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                }, 200);
            }
        },

        undo(id) {
            const toast = this.toasts.find(t => t.id === id);
            if (toast && toast.undo) {
                toast.undo();
                this.dismiss(id);
            }
        },

        init() {
            // Listen for custom toast events
            window.addEventListener('toast:show', (e) => {
                this.show(e.detail.type, e.detail.title, e.detail.message, e.detail.options);
            });

            window.addEventListener('toast:success', (e) => {
                this.success(e.detail.title, e.detail.message, e.detail.options);
            });

            window.addEventListener('toast:error', (e) => {
                this.error(e.detail.title, e.detail.message, e.detail.options);
            });

            window.addEventListener('toast:warning', (e) => {
                this.warning(e.detail.title, e.detail.message, e.detail.options);
            });

            window.addEventListener('toast:info', (e) => {
                this.info(e.detail.title, e.detail.message, e.detail.options);
            });
        }
    }
}

// Global convenience functions
window.showToast = (type, title, message, options) => {
    window.dispatchEvent(new CustomEvent('toast:show', { detail: { type, title, message, options } }));
};

window.showSuccessToast = (title, message, options) => {
    window.dispatchEvent(new CustomEvent('toast:success', { detail: { title, message, options } }));
};

window.showErrorToast = (title, message, options) => {
    window.dispatchEvent(new CustomEvent('toast:error', { detail: { title, message, options } }));
};

window.showWarningToast = (title, message, options) => {
    window.dispatchEvent(new CustomEvent('toast:warning', { detail: { title, message, options } }));
};

window.showInfoToast = (title, message, options) => {
    window.dispatchEvent(new CustomEvent('toast:info', { detail: { title, message, options } }));
};
</script>
