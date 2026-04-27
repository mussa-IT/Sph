@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('meta_description', 'Platform administration panel with analytics, user management, and system monitoring.')

@php
    $pageTitle = 'Admin';
    $pageHeading = 'Operations';
@endphp

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-muted dark:text-muted-dark">
                    Last updated: {{ now()->format('M j, Y g:i A') }}
                </p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.users.index') }}" class="btn-brand interactive-lift px-4">
                    Manage Users
                </a>
            </div>
        </div>

        {{-- Key Metrics --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <x-stat-card icon="👥" title="Total Users" value="{{ number_format($metrics['users']) }}" color="primary" />
            <x-stat-card icon="🔥" title="Active Users" value="{{ number_format($metrics['active_users']) }}" color="emerald" change="7 days" changeType="neutral" />
            <x-stat-card icon="📁" title="Total Projects" value="{{ number_format($metrics['projects']) }}" color="blue" />
            <x-stat-card icon="🤖" title="AI Replies" value="{{ number_format($metrics['ai_messages']) }}" color="violet" change="{{ number_format($metrics['ai_messages_last_7_days']) }} this week" changeType="positive" />
            <x-stat-card icon="🚩" title="Flagged Content" value="{{ number_format($metrics['flagged_content']) }}" color="rose" />
        </section>

        {{-- Charts Row --}}
        <section class="grid gap-6 lg:grid-cols-2">
            {{-- Daily Signups Chart --}}
            <div class="chart-panel">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-foreground dark:text-foreground-dark">Daily Signups</h3>
                        <p class="text-xs text-muted dark:text-muted-dark">New user registrations over the last 14 days</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-semibold text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        {{ $analytics['daily_signups']['today'] }} today
                    </span>
                </div>
                <div class="h-64 sm:h-72">
                    <canvas id="signupsChart"></canvas>
                </div>
            </div>

            {{-- Feature Usage Chart --}}
            <div class="chart-panel">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-foreground dark:text-foreground-dark">Feature Usage</h3>
                        <p class="text-xs text-muted dark:text-muted-dark">Most used platform features</p>
                    </div>
                </div>
                <div class="h-64 sm:h-72">
                    <canvas id="featuresChart"></canvas>
                </div>
            </div>
        </section>

        {{-- AI Stats & Flagged Content --}}
        <section class="grid gap-6 lg:grid-cols-[2fr_1.1fr]">
            <div class="rounded-2xl border border-muted/20 bg-background p-6 shadow-card dark:border-muted-dark/20 dark:bg-background-dark">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-foreground dark:text-foreground-dark">AI Usage Stats</h2>
                        <p class="mt-1 text-sm text-muted dark:text-muted-dark">Track AI activity and content generation at a glance.</p>
                    </div>
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-muted/10 bg-background-secondary p-4 dark:border-muted-dark/10 dark:bg-background-secondary-dark">
                        <p class="text-xs uppercase tracking-[.24em] text-muted dark:text-muted-dark">AI Replies</p>
                        <p class="mt-2 text-2xl font-semibold text-foreground dark:text-foreground-dark">{{ number_format($metrics['ai_messages']) }}</p>
                    </div>
                    <div class="rounded-2xl border border-muted/10 bg-background-secondary p-4 dark:border-muted-dark/10 dark:bg-background-secondary-dark">
                        <p class="text-xs uppercase tracking-[.24em] text-muted dark:text-muted-dark">AI Sessions</p>
                        <p class="mt-2 text-2xl font-semibold text-foreground dark:text-foreground-dark">{{ number_format($metrics['ai_sessions']) }}</p>
                    </div>
                </div>

                <div class="mt-6 rounded-2xl border border-muted/10 bg-background-secondary p-4 dark:border-muted-dark/10 dark:bg-background-secondary-dark">
                    <p class="text-xs uppercase tracking-[.24em] text-muted dark:text-muted-dark">Recent AI Activity</p>
                    <div class="mt-4 space-y-3 text-sm text-foreground dark:text-foreground-dark">
                        <div class="flex items-center justify-between gap-3">
                            <span>AI replies in last 7 days</span>
                            <span class="font-semibold">{{ number_format($metrics['ai_messages_last_7_days']) }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Active users in last 7 days</span>
                            <span class="font-semibold">{{ number_format($metrics['active_users']) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-muted/20 bg-background p-6 shadow-card dark:border-muted-dark/20 dark:bg-background-dark">
                <h2 class="text-lg font-semibold text-foreground dark:text-foreground-dark">Flagged Content</h2>
                <p class="mt-1 text-sm text-muted dark:text-muted-dark">Review recently flagged AI messages and content items.</p>

                <div class="mt-6 space-y-4">
                    @if (count($recentFlagged) > 0)
                        @foreach ($recentFlagged as $flagged)
                            <div class="rounded-3xl border border-muted/10 bg-background-secondary p-4 dark:border-muted-dark/10 dark:bg-background-secondary-dark">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-sm font-semibold text-foreground dark:text-foreground-dark">{{ $flagged->chatSession?->title ?? 'Chat session' }}</p>
                                    <span class="text-xs text-muted dark:text-muted-dark">{{ $flagged->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="mt-3 text-sm text-muted dark:text-muted-dark overflow-hidden break-words max-h-20">{{ $flagged->message }}</p>
                            </div>
                        @endforeach
                    @else
                        <div class="rounded-3xl border border-muted/10 bg-background-secondary p-4 text-sm text-muted dark:border-muted-dark/10 dark:bg-background-secondary-dark dark:text-muted-dark">
                            No flagged content has been recorded yet.
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-muted/20 bg-background p-6 shadow-card dark:border-muted-dark/20 dark:bg-background-dark">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-foreground dark:text-foreground-dark">Product Analytics</h2>
                    <p class="text-sm text-muted dark:text-muted-dark">Daily signups, feature usage, retention, and completion trends.</p>
                </div>
                <span class="text-xs text-muted dark:text-muted-dark">Last 14 days</span>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-muted/10 bg-background-secondary p-4 dark:border-muted-dark/10 dark:bg-background-secondary-dark">
                    <p class="text-xs uppercase tracking-[.2em] text-muted dark:text-muted-dark">Daily Signups</p>
                    <p class="mt-2 text-2xl font-semibold text-foreground dark:text-foreground-dark">{{ number_format($analytics['daily_signups']['today']) }}</p>
                    <p class="mt-1 text-xs text-muted dark:text-muted-dark">{{ number_format($analytics['daily_signups']['last_7_days']) }} in the last 7 days</p>
                </div>
                <div class="rounded-2xl border border-muted/10 bg-background-secondary p-4 dark:border-muted-dark/10 dark:bg-background-secondary-dark">
                    <p class="text-xs uppercase tracking-[.2em] text-muted dark:text-muted-dark">Retention (7d / 30d)</p>
                    <p class="mt-2 text-2xl font-semibold text-foreground dark:text-foreground-dark">{{ number_format($analytics['retention']['rolling_7_day'], 1) }}% / {{ number_format($analytics['retention']['rolling_30_day'], 1) }}%</p>
                    <p class="mt-1 text-xs text-muted dark:text-muted-dark">Rolling active user retention</p>
                </div>
                <div class="rounded-2xl border border-muted/10 bg-background-secondary p-4 dark:border-muted-dark/10 dark:bg-background-secondary-dark">
                    <p class="text-xs uppercase tracking-[.2em] text-muted dark:text-muted-dark">Cohort Retention</p>
                    <p class="mt-2 text-2xl font-semibold text-foreground dark:text-foreground-dark">{{ number_format($analytics['retention']['cohort_7_day'], 1) }}% / {{ number_format($analytics['retention']['cohort_30_day'], 1) }}%</p>
                    <p class="mt-1 text-xs text-muted dark:text-muted-dark">7d cohort / 30d cohort</p>
                </div>
                <div class="rounded-2xl border border-muted/10 bg-background-secondary p-4 dark:border-muted-dark/10 dark:bg-background-secondary-dark">
                    <p class="text-xs uppercase tracking-[.2em] text-muted dark:text-muted-dark">Project Completion</p>
                    <p class="mt-2 text-2xl font-semibold text-foreground dark:text-foreground-dark">{{ number_format($analytics['project_completion_rate']['rate'], 1) }}%</p>
                    <p class="mt-1 text-xs text-muted dark:text-muted-dark">{{ number_format($analytics['project_completion_rate']['completed']) }} of {{ number_format($analytics['project_completion_rate']['total']) }} projects complete</p>
                </div>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-2">
                <div class="rounded-2xl border border-muted/10 bg-background-secondary p-4 dark:border-muted-dark/10 dark:bg-background-secondary-dark">
                    <h3 class="text-sm font-semibold text-foreground dark:text-foreground-dark">Daily Signup Trend</h3>
                    <div class="mt-3 space-y-2">
                        @foreach($analytics['daily_signups']['series'] as $day)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted dark:text-muted-dark">{{ \Illuminate\Support\Carbon::parse($day['date'])->format('M j') }}</span>
                                <span class="font-medium text-foreground dark:text-foreground-dark">{{ number_format($day['count']) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-2xl border border-muted/10 bg-background-secondary p-4 dark:border-muted-dark/10 dark:bg-background-secondary-dark">
                    <h3 class="text-sm font-semibold text-foreground dark:text-foreground-dark">Most Used Features</h3>
                    <div class="mt-3 space-y-2">
                        @foreach($analytics['most_used_features'] as $feature)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted dark:text-muted-dark">{{ $feature['name'] }}</span>
                                <span class="font-medium text-foreground dark:text-foreground-dark">{{ number_format($feature['count']) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-muted/20 bg-background p-6 shadow-card dark:border-muted-dark/20 dark:bg-background-dark">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-foreground dark:text-foreground-dark">Recent Signups</h2>
                    <p class="mt-1 text-sm text-muted dark:text-muted-dark">Latest users who joined your platform.</p>
                </div>
                <span class="rounded-full bg-muted/10 px-3 py-1 text-xs font-semibold text-muted dark:bg-muted-dark/10 dark:text-muted-dark">{{ number_format($recentUsers->count()) }} new</span>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs uppercase tracking-[.12em] text-muted dark:text-muted-dark">
                        <tr>
                            <th class="pb-3">Name</th>
                            <th class="pb-3">Email</th>
                            <th class="pb-3">Joined</th>
                            <th class="pb-3">Verified</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-muted/20 dark:divide-muted-dark/20">
                        @foreach ($recentUsers as $user)
                            <tr>
                                <td class="py-3 text-foreground dark:text-foreground-dark">{{ $user->name }}</td>
                                <td class="py-3 text-muted dark:text-muted-dark">{{ $user->email }}</td>
                                <td class="py-3 text-muted dark:text-muted-dark">{{ $user->created_at->diffForHumans() }}</td>
                                <td class="py-3">
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $user->email_verified_at ? 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300' : 'bg-amber-500/15 text-amber-700 dark:text-amber-300' }}">
                                        {{ $user->email_verified_at ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    {{-- Analytics Charts --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const c = window.chartColors || {
            primary: '#7c3aed',
            secondary: '#2563eb',
            violet: '#8b5cf6',
            blue: '#3b82f6',
            emerald: '#10b981',
            amber: '#f59e0b',
            rose: '#f43f5e',
            foreground: '#0f172a',
            muted: '#64748b',
            grid: 'rgba(100,116,139,0.15)',
            background: '#ffffff'
        };

        const isMobile = window.matchMedia('(max-width: 640px)').matches;

        // Daily Signups Chart
        @if(!empty($analytics['daily_signups']['series']))
        const signupData = @json($analytics['daily_signups']['series']);
        new Chart(document.getElementById('signupsChart'), {
            type: 'line',
            data: {
                labels: signupData.map(d => {
                    const date = new Date(d.date);
                    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                }),
                datasets: [{
                    label: 'New Signups',
                    data: signupData.map(d => d.count),
                    borderColor: c.primary,
                    backgroundColor: 'rgba(124,58,237,0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointRadius: isMobile ? 3 : 4,
                    pointHoverRadius: isMobile ? 5 : 6,
                    pointBackgroundColor: c.primary,
                    pointBorderColor: c.background,
                    pointBorderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: c.background,
                        titleColor: c.foreground,
                        bodyColor: c.muted,
                        borderColor: c.grid,
                        borderWidth: 1,
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false,
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: c.muted, maxRotation: 45, font: { size: isMobile ? 10 : 11 } }
                    },
                    y: {
                        grid: { color: c.grid },
                        ticks: { color: c.muted, font: { size: isMobile ? 10 : 11 }, stepSize: 1 }
                    }
                }
            }
        });
        @endif

        // Feature Usage Doughnut Chart
        @if(!empty($analytics['most_used_features']))
        const featureData = @json($analytics['most_used_features']);
        const colors = [c.violet, c.blue, c.emerald, c.amber, c.rose, c.secondary];
        new Chart(document.getElementById('featuresChart'), {
            type: 'doughnut',
            data: {
                labels: featureData.map(f => f.name),
                datasets: [{
                    data: featureData.map(f => f.count),
                    backgroundColor: colors,
                    borderColor: c.background,
                    borderWidth: 3,
                    hoverOffset: isMobile ? 5 : 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            boxWidth: isMobile ? 8 : 10,
                            pointStyle: 'circle',
                            color: c.foreground,
                            font: { size: isMobile ? 10 : 12 },
                            padding: isMobile ? 10 : 15
                        }
                    },
                    tooltip: {
                        backgroundColor: c.background,
                        titleColor: c.foreground,
                        bodyColor: c.muted,
                        borderColor: c.grid,
                        borderWidth: 1,
                        padding: 12,
                        cornerRadius: 8,
                    }
                }
            }
        });
        @endif
    });
    </script>
@endsection
