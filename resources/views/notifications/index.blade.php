@extends('layouts.app')

@section('title', 'Notifications')

@php
    $pageTitle = 'Notifications';
    $pageHeading = 'Inbox';

    $filters = [
        'all' => 'All',
        'unread' => 'Unread',
        'projects' => 'Projects',
        'tasks' => 'Tasks',
        'system' => 'System',
    ];

    $typeLabels = [
        'project_reminders' => 'Project',
        'task_deadlines' => 'Task',
        'ai_updates' => 'System',
        'system_alerts' => 'System',
    ];

    $typeClasses = [
        'project_reminders' => 'bg-sky-500/10 text-sky-700 dark:text-sky-300',
        'task_deadlines' => 'bg-amber-500/10 text-amber-700 dark:text-amber-300',
        'ai_updates' => 'bg-violet-500/10 text-violet-700 dark:text-violet-300',
        'system_alerts' => 'bg-rose-500/10 text-rose-700 dark:text-rose-300',
    ];
@endphp

@section('content')
    <div class="space-y-4">
        <div class="rounded-2xl border border-muted/20 bg-background p-4 dark:border-muted-dark/20 dark:bg-background-dark">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-5">
                    @foreach ($filters as $filterKey => $label)
                        <a
                            href="{{ route('notifications.index', ['filter' => $filterKey]) }}"
                            class="inline-flex min-h-[44px] items-center justify-between gap-2 rounded-xl px-3 py-2 text-sm font-medium transition {{ $activeFilter === $filterKey ? 'bg-primary text-primary-foreground shadow-soft' : 'border border-muted/20 bg-background-secondary text-foreground hover:bg-muted/10 dark:border-muted-dark/20 dark:bg-background-secondary-dark dark:text-foreground-dark dark:hover:bg-muted-dark/10' }}"
                        >
                            <span>{{ $label }}</span>
                            <span class="{{ $activeFilter === $filterKey ? 'text-primary-foreground/80' : 'text-muted dark:text-muted-dark' }}">
                                {{ $filterCounts[$filterKey] ?? 0 }}
                            </span>
                        </a>
                    @endforeach
                </div>

                <button
                    type="button"
                    id="notifications-mark-all"
                    class="inline-flex min-h-[44px] items-center justify-center rounded-xl border border-muted/20 bg-background-secondary px-4 py-2 text-sm font-semibold text-foreground transition hover:bg-muted/10 dark:border-muted-dark/20 dark:bg-background-secondary-dark dark:text-foreground-dark dark:hover:bg-muted-dark/10"
                >
                    Mark all as read
                </button>
            </div>
        </div>

        <div class="rounded-2xl border border-muted/20 bg-background dark:border-muted-dark/20 dark:bg-background-dark">
            @if ($notifications->isEmpty())
                <div class="px-6 py-16 text-center">
                    <p class="text-base font-semibold text-foreground dark:text-foreground-dark">No notifications</p>
                    <p class="mt-2 text-sm text-muted dark:text-muted-dark">You are all caught up for this filter.</p>
                </div>
            @else
                <div class="divide-y divide-muted/20 dark:divide-muted-dark/20">
                    @foreach ($notifications as $notification)
                        <article
                            class="notification-row flex flex-col gap-3 px-6 py-5 sm:flex-row sm:items-start sm:justify-between {{ $notification->read ? 'opacity-70' : '' }}"
                            data-notification-id="{{ $notification->id }}"
                            data-notification-read="{{ $notification->read ? '1' : '0' }}"
                        >
                            <div class="min-w-0 space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    @if (! $notification->read)
                                        <span class="h-2.5 w-2.5 rounded-full bg-primary" aria-hidden="true"></span>
                                    @endif
                                    <h2 class="text-sm font-semibold text-foreground dark:text-foreground-dark">{{ $notification->title }}</h2>
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $typeClasses[$notification->type] ?? 'bg-muted/20 text-muted dark:bg-muted-dark/20 dark:text-muted-dark' }}">
                                        {{ $typeLabels[$notification->type] ?? 'General' }}
                                    </span>
                                </div>
                                <p class="text-sm text-muted dark:text-muted-dark">{{ $notification->message }}</p>
                                <p class="text-xs text-muted dark:text-muted-dark">{{ $notification->created_at?->diffForHumans() }}</p>
                            </div>

                            @if (! $notification->read)
                                <button
                                    type="button"
                                    class="mark-read-btn inline-flex min-h-[40px] items-center justify-center rounded-lg border border-primary/20 bg-primary/10 px-3 py-1.5 text-xs font-semibold text-primary transition hover:bg-primary/20"
                                    data-notification-id="{{ $notification->id }}"
                                >
                                    Mark read
                                </button>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </div>

        @if ($notifications->hasPages())
            <div>
                {{ $notifications->links() }}
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const markAllBtn = document.getElementById('notifications-mark-all');

            const markAsRead = async (notificationId) => {
                if (!notificationId || !token) {
                    return;
                }

                const response = await fetch(`{{ url('/notifications') }}/${notificationId}/read`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    },
                });

                if (response.ok) {
                    window.location.reload();
                }
            };

            document.querySelectorAll('.mark-read-btn').forEach((button) => {
                button.addEventListener('click', async () => {
                    await markAsRead(button.dataset.notificationId);
                });
            });

            markAllBtn?.addEventListener('click', async () => {
                if (!token) {
                    return;
                }

                const response = await fetch(`{{ route('notifications.mark-all-read') }}`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    },
                });

                if (response.ok) {
                    window.location.reload();
                }
            });
        });
    </script>
@endsection
