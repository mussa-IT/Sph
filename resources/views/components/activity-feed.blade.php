{{-- Real-time Activity Feed Component --}}
<div x-data="activityFeed()" x-init="init()" class="rounded-2xl border border-muted/10 bg-background p-6 shadow-card dark:border-muted-dark/10 dark:bg-background-dark">
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h3 class="text-lg font-bold text-foreground dark:text-foreground-dark">Activity Feed</h3>
            <p class="text-xs text-muted dark:text-muted-dark">Real-time updates from your workspace</p>
        </div>
        <div class="flex items-center gap-2">
            <span x-show="unreadCount > 0" x-text="unreadCount + ' new'" class="rounded-full bg-primary/10 px-2.5 py-1 text-xs font-semibold text-primary dark:bg-primary/20"></span>
            <button x-on:click="markAllRead()" x-show="activities.length > 0" class="text-xs font-medium text-muted hover:text-primary dark:text-muted-dark dark:hover:text-primary">
                Mark all read
            </button>
            <button x-on:click="refresh()" x-bind:disabled="loading" class="rounded-lg p-2 text-muted hover:bg-muted/10 hover:text-primary disabled:opacity-50 dark:text-muted-dark dark:hover:bg-muted-dark/10 dark:hover:text-primary">
                <svg x-bind:class="loading && 'animate-spin'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Filter Tabs --}}
    <div class="mb-4 flex gap-1 border-b border-muted/10 dark:border-muted-dark/10">
        <template x-for="filter in filters" :key="filter.id">
            <button
                x-on:click="currentFilter = filter.id"
                x-bind:class="currentFilter === filter.id 
                    ? 'border-b-2 border-primary text-primary' 
                    : 'text-muted hover:text-foreground dark:text-muted-dark dark:hover:text-foreground-dark'"
                class="px-3 py-2 text-sm font-medium transition-colors"
                x-text="filter.label"
            ></button>
        </template>
    </div>

    {{-- Activity List --}
    <div class="space-y-3 max-h-[400px] overflow-y-auto pr-1">
        <template x-for="activity in filteredActivities" :key="activity.id">
            <div
                x-bind:class="activity.read ? 'opacity-60' : ''"
                class="group relative rounded-xl border border-muted/5 bg-background-secondary p-4 transition-all duration-300 hover:border-primary/20 hover:shadow-sm dark:border-muted-dark/5 dark:bg-background-secondary-dark"
            >
                <div class="flex items-start gap-3">
                    {{-- Icon/Avatar --}}
                    <div class="relative shrink-0">
                        <div x-bind:class="activity.iconBg" class="flex h-10 w-10 items-center justify-center rounded-xl text-lg">
                            <span x-text="activity.icon"></span>
                        </div>
                        <span x-show="!activity.read" class="absolute -right-0.5 -top-0.5 h-2.5 w-2.5 rounded-full bg-primary ring-2 ring-background dark:ring-background-dark"></span>
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-foreground dark:text-foreground-dark">
                            <span x-html="activity.message" class="leading-relaxed"></span>
                        </p>
                        <div class="mt-1 flex items-center gap-2">
                            <span x-text="timeAgo(activity.timestamp)" class="text-xs text-muted dark:text-muted-dark"></span>
                            <span x-show="activity.projectName" x-text="activity.projectName" class="rounded bg-muted/20 px-1.5 py-0.5 text-xs text-muted dark:bg-muted-dark/20 dark:text-muted-dark"></span>
                        </div>
                    </div>

                    {{-- Actions --}
                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button x-show="!activity.read" x-on:click="markRead(activity.id)" class="rounded-lg p-1.5 text-muted hover:bg-muted/10 hover:text-primary dark:text-muted-dark dark:hover:bg-muted-dark/10" title="Mark as read">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </button>
                        <a x-show="activity.url" x-bind:href="activity.url" class="rounded-lg p-1.5 text-muted hover:bg-muted/10 hover:text-primary dark:text-muted-dark dark:hover:bg-muted-dark/10" title="View">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </template>

        {{-- Empty State --}}
        <div x-show="filteredActivities.length === 0" class="py-8 text-center">
            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-muted/10 dark:bg-muted-dark/10">
                <svg class="h-6 w-6 text-muted dark:text-muted-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
            </div>
            <p class="text-sm text-muted dark:text-muted-dark">No activities to show</p>
        </div>

        {{-- Loading State --}
        <div x-show="loading && activities.length === 0" class="space-y-3">
            <template x-for="i in 3" :key="i">
                <div class="rounded-xl bg-background-secondary p-4 animate-pulse dark:bg-background-secondary-dark">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-muted/20 dark:bg-muted-dark/20"></div>
                        <div class="flex-1 space-y-2">
                            <div class="h-4 w-3/4 rounded bg-muted/20 dark:bg-muted-dark/20"></div>
                            <div class="h-3 w-1/2 rounded bg-muted/20 dark:bg-muted-dark/20"></div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Load More --}}
    <div x-show="hasMore" class="mt-4 text-center">
        <button x-on:click="loadMore()" x-bind:disabled="loading" class="btn-brand-muted px-4 py-2 text-sm">
            <span x-show="!loading">Load more</span>
            <span x-show="loading">Loading...</span>
        </button>
    </div>
</div>

<script>
function activityFeed() {
    return {
        activities: [],
        loading: false,
        hasMore: false,
        currentFilter: 'all',
        pollInterval: null,
        lastPoll: Date.now(),
        
        filters: [
            { id: 'all', label: 'All' },
            { id: 'project', label: 'Projects' },
            { id: 'task', label: 'Tasks' },
            { id: 'system', label: 'System' },
        ],
        
        get filteredActivities() {
            if (this.currentFilter === 'all') return this.activities;
            return this.activities.filter(a => a.type === this.currentFilter);
        },
        
        get unreadCount() {
            return this.activities.filter(a => !a.read).length;
        },
        
        async init() {
            await this.refresh();
            this.startPolling();
            
            // Listen for real-time updates
            window.addEventListener('focus', () => {
                if (Date.now() - this.lastPoll > 60000) {
                    this.refresh();
                }
            });
        },
        
        startPolling() {
            // Poll every 30 seconds for new activities
            this.pollInterval = setInterval(() => this.checkNew(), 30000);
        },
        
        async refresh() {
            this.loading = true;
            try {
                const response = await fetch('/api/activities?limit=20');
                const data = await response.json();
                this.activities = data.activities || [];
                this.hasMore = data.hasMore || false;
                this.lastPoll = Date.now();
            } catch (error) {
                console.error('Failed to load activities:', error);
            } finally {
                this.loading = false;
            }
        },
        
        async checkNew() {
            if (document.hidden) return;
            
            try {
                const lastId = this.activities[0]?.id;
                const response = await fetch(`/api/activities/check?after=${lastId}`);
                const data = await response.json();
                
                if (data.new?.length > 0) {
                    // Add new activities to the top
                    this.activities = [...data.new, ...this.activities];
                    
                    // Show notification for new items
                    if (data.new.length > 0) {
                        this.showNotification(data.new[0]);
                    }
                }
                this.lastPoll = Date.now();
            } catch (error) {
                console.error('Failed to check for new activities:', error);
            }
        },
        
        async loadMore() {
            this.loading = true;
            try {
                const lastId = this.activities[this.activities.length - 1]?.id;
                const response = await fetch(`/api/activities?before=${lastId}&limit=20`);
                const data = await response.json();
                
                this.activities = [...this.activities, ...(data.activities || [])];
                this.hasMore = data.hasMore || false;
            } catch (error) {
                console.error('Failed to load more activities:', error);
            } finally {
                this.loading = false;
            }
        },
        
        async markRead(id) {
            const activity = this.activities.find(a => a.id === id);
            if (activity) {
                activity.read = true;
                
                try {
                    await fetch(`/api/activities/${id}/read`, { method: 'POST' });
                } catch (error) {
                    console.error('Failed to mark as read:', error);
                }
            }
        },
        
        async markAllRead() {
            this.activities.forEach(a => a.read = true);
            
            try {
                await fetch('/api/activities/mark-all-read', { method: 'POST' });
            } catch (error) {
                console.error('Failed to mark all as read:', error);
            }
        },
        
        timeAgo(timestamp) {
            const seconds = Math.floor((Date.now() - new Date(timestamp).getTime()) / 1000);
            
            let interval = seconds / 31536000;
            if (interval > 1) return Math.floor(interval) + 'y ago';
            
            interval = seconds / 2592000;
            if (interval > 1) return Math.floor(interval) + 'mo ago';
            
            interval = seconds / 86400;
            if (interval > 1) return Math.floor(interval) + 'd ago';
            
            interval = seconds / 3600;
            if (interval > 1) return Math.floor(interval) + 'h ago';
            
            interval = seconds / 60;
            if (interval > 1) return Math.floor(interval) + 'm ago';
            
            return 'Just now';
        },
        
        showNotification(activity) {
            // Browser notification if permitted
            if (Notification.permission === 'granted') {
                new Notification('Smart Project Hub', {
                    body: activity.message.replace(/<[^>]*>/g, ''),
                    icon: '/favicon.ico'
                });
            }
        },
        
        destroy() {
            if (this.pollInterval) {
                clearInterval(this.pollInterval);
            }
        }
    }
}
</script>
