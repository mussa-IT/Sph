@extends('layouts.app')

@section('title', 'Dashboard')

@php
    $pageTitle = 'Dashboard';
    $pageHeading = 'Overview';
@endphp

@section('content')
    <!-- Welcome Header -->
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight text-foreground dark:text-foreground-dark">
                Welcome back, {{ auth()->user()->name ?? 'User' }}
            </h1>
            <p class="mt-2 text-muted dark:text-muted-dark">Here's everything happening in your workspace today.</p>
        </div>
        <a href="{{ route('projects.create') }}" class="btn-brand touch-target inline-flex gap-2 rounded-xl">
            <span>➕</span> New Project
        </a>
    </div>

    <!-- Stat Cards -->
    <div class="mb-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card icon="📁" title="Projects" value="12" change="+3 this week" changeType="positive" color="violet" />
        <x-stat-card icon="✅" title="Active Tasks" value="48" change="8 pending" changeType="neutral" color="blue" />
        <x-stat-card icon="🤖" title="AI Sessions" value="24" change="+5 today" changeType="positive" color="emerald" />
        <x-stat-card icon="💰" title="Budget Used" value="$12.4k" change="of $20k" changeType="neutral" color="amber" />
    </div>

    <!-- Charts Row -->
    <div class="mb-8 grid gap-6 lg:grid-cols-3">
        <!-- Line Chart: Project Progress -->
        <div class="chart-panel lg:col-span-2">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-bold text-foreground dark:text-foreground-dark">Project Progress</h3>
                    <p class="text-xs text-muted dark:text-muted-dark">Tasks completed over the last 6 months</p>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-semibold text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> +12% vs last month
                </span>
            </div>
            <div class="h-64 sm:h-72">
                <canvas id="progressChart"></canvas>
            </div>
        </div>

        <!-- Pie Chart: Project Categories -->
        <div class="chart-panel">
            <div class="mb-4">
                <h3 class="text-lg font-bold text-foreground dark:text-foreground-dark">Categories</h3>
                <p class="text-xs text-muted dark:text-muted-dark">Projects by type</p>
            </div>
            <div class="h-52 sm:h-56">
                <canvas id="categoryChart"></canvas>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-2">
                <div class="flex items-center gap-2 text-xs">
                    <span class="h-2.5 w-2.5 rounded-full bg-violet-500"></span>
                    <span class="text-muted dark:text-muted-dark">Web Dev</span>
                </div>
                <div class="flex items-center gap-2 text-xs">
                    <span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span>
                    <span class="text-muted dark:text-muted-dark">Mobile</span>
                </div>
                <div class="flex items-center gap-2 text-xs">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                    <span class="text-muted dark:text-muted-dark">AI/ML</span>
                </div>
                <div class="flex items-center gap-2 text-xs">
                    <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                    <span class="text-muted dark:text-muted-dark">Design</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Bar Chart + Table Row -->
    <div class="grid gap-6 lg:grid-cols-3">
        <!-- Budget Bar Chart -->
        <div class="chart-panel">
            <div class="mb-4">
                <h3 class="text-lg font-bold text-foreground dark:text-foreground-dark">Budget Overview</h3>
                <p class="text-xs text-muted dark:text-muted-dark">Spending by project category</p>
            </div>
            <div class="h-56 sm:h-64">
                <canvas id="budgetChart"></canvas>
            </div>
        </div>

        <!-- Recent Projects Table -->
        <div class="lg:col-span-2 rounded-2xl border border-muted/10 bg-background p-6 shadow-card dark:border-muted-dark/10 dark:bg-background-dark">
            <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-bold text-foreground dark:text-foreground-dark">Recent Projects</h3>
                    <p class="text-xs text-muted dark:text-muted-dark">Your most active projects this month</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="relative w-full sm:w-auto">
                        <input type="text" placeholder="Search projects..."
                            class="pl-9 pr-4 py-2 h-10 w-full sm:w-56 rounded-xl border border-muted/20 bg-background-secondary text-sm text-foreground outline-none transition-all duration-200 placeholder:text-muted/50 focus:border-primary focus:ring-2 focus:ring-primary/10 dark:border-muted-dark/20 dark:bg-background-secondary-dark dark:text-foreground-dark dark:placeholder:text-muted-dark/50">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    </div>
                    <a href="{{ route('projects.index') }}" class="hidden sm:inline-flex h-10 items-center px-4 text-sm font-semibold text-primary transition hover:text-primary/80 dark:text-primary dark:hover:text-primary/80">View all</a>
                </div>
            </div>
            <div class="table-scroll-x rounded-xl border border-muted/10 dark:border-muted-dark/10">
                <table class="min-w-[760px] w-full text-left">
                    <thead class="bg-muted/5 dark:bg-muted-dark/5">
                        <tr>
                            <th class="px-5 py-4 text-xs font-bold uppercase tracking-wider text-muted dark:text-muted-dark">Project</th>
                            <th class="px-5 py-4 text-xs font-bold uppercase tracking-wider text-muted dark:text-muted-dark">Category</th>
                            <th class="px-5 py-4 text-xs font-bold uppercase tracking-wider text-muted dark:text-muted-dark">Progress</th>
                            <th class="px-5 py-4 text-xs font-bold uppercase tracking-wider text-muted dark:text-muted-dark">Budget</th>
                            <th class="px-5 py-4 text-xs font-bold uppercase tracking-wider text-muted dark:text-muted-dark">Status</th>
                            <th class="px-5 py-4 text-xs font-bold uppercase tracking-wider text-muted dark:text-muted-dark">Deadline</th>
                            <th class="px-5 py-4 text-xs font-bold uppercase tracking-wider text-muted dark:text-muted-dark"></th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @foreach([
                            ['name' => 'Website Redesign', 'category' => 'Web Dev', 'categoryColor' => 'violet', 'progress' => 75, 'budget' => '$8,400', 'budgetTotal' => '$12k', 'status' => 'Active', 'statusColor' => 'bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400', 'deadline' => 'May 15'],
                            ['name' => 'Mobile App Launch', 'category' => 'Mobile', 'categoryColor' => 'blue', 'progress' => 30, 'budget' => '$2,100', 'budgetTotal' => '$15k', 'status' => 'Planning', 'statusColor' => 'bg-amber-500/10 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400', 'deadline' => 'Jun 01'],
                            ['name' => 'AI Chat Integration', 'category' => 'AI/ML', 'categoryColor' => 'emerald', 'progress' => 60, 'budget' => '$5,700', 'budgetTotal' => '$8k', 'status' => 'Active', 'statusColor' => 'bg-blue-500/10 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400', 'deadline' => 'May 30'],
                            ['name' => 'Budget Review Q2', 'category' => 'Admin', 'categoryColor' => 'rose', 'progress' => 90, 'budget' => '$0', 'budgetTotal' => '$2k', 'status' => 'Review', 'statusColor' => 'bg-violet-500/10 text-violet-600 dark:bg-violet-500/20 dark:text-violet-400', 'deadline' => 'Apr 28'],
                            ['name' => 'Team Onboarding', 'category' => 'Admin', 'categoryColor' => 'amber', 'progress' => 100, 'budget' => '$1,200', 'budgetTotal' => '$1,5k', 'status' => 'Completed', 'statusColor' => 'bg-muted/10 text-muted dark:bg-muted-dark/20 dark:text-muted-dark', 'deadline' => 'Apr 20'],
                        ] as $project)
                        <tr class="group border-t border-muted/5 transition-all duration-200 hover:bg-muted/5 dark:border-muted-dark/5 dark:hover:bg-muted-dark/5">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-primary/20 to-secondary/20 flex items-center justify-center text-sm font-bold text-primary dark:from-primary/30 dark:to-secondary/30 group-hover:scale-110 transition-transform duration-300">
                                        {{ strtoupper(substr($project['name'], 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-foreground dark:text-foreground-dark">{{ $project['name'] }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center rounded-full bg-{{ $project['categoryColor'] }}-500/10 px-2.5 py-1 text-xs font-semibold text-{{ $project['categoryColor'] }}-600 dark:bg-{{ $project['categoryColor'] }}-500/20 dark:text-{{ $project['categoryColor'] }}-400">
                                    {{ $project['category'] }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-2 w-28 overflow-hidden rounded-full bg-muted/20 dark:bg-muted-dark/20">
                                        <div class="h-full rounded-full bg-gradient-to-r from-primary to-secondary transition-all duration-500 group-hover:from-primary group-hover:to-primary" style="width: {{ $project['progress'] }}%"></div>
                                    </div>
                                    <span class="text-xs font-semibold text-muted dark:text-muted-dark w-10">{{ $project['progress'] }}%</span>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div>
                                    <p class="font-semibold text-foreground dark:text-foreground-dark">{{ $project['budget'] }}</p>
                                    <p class="text-xs text-muted dark:text-muted-dark">of {{ $project['budgetTotal'] }}</p>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $project['statusColor'] }}">
                                    {{ $project['status'] }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-sm font-medium text-muted dark:text-muted-dark">{{ $project['deadline'] }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button class="touch-target h-9 w-9 inline-flex items-center justify-center rounded-lg text-muted hover:bg-muted/10 hover:text-primary dark:text-muted-dark dark:hover:bg-muted-dark/10 dark:hover:text-primary transition-all">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                                    </button>
                                    <button class="touch-target h-9 w-9 inline-flex items-center justify-center rounded-lg text-muted hover:bg-muted/10 hover:text-primary dark:text-muted-dark dark:hover:bg-muted-dark/10 dark:hover:text-primary transition-all">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M16.862 4.487a4.875 4.875 0 0 1 6.88 6.88l-1.5 1.5M13.12 8.232a3.125 3.125 0 0 1 4.41 4.41l-7.5 7.5a2 2 0 0 1-1.023.542l-4.042.808a.5.5 0 0 1-.602-.602l.808-4.042a2 2 0 0 1 .542-1.023z"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bottom Row -->
    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        <!-- Activity Feed -->
        <div class="lg:col-span-2 rounded-2xl border border-muted/10 bg-background p-6 shadow-card dark:border-muted-dark/10 dark:bg-background-dark">
            <div class="mb-6 flex items-center justify-between">
                <h3 class="text-lg font-bold text-foreground dark:text-foreground-dark">Recent Activity</h3>
                <button class="text-sm font-semibold text-primary transition hover:text-primary/80 dark:text-primary dark:hover:text-primary/80">View all</button>
            </div>
            <div class="space-y-3">
                @foreach([
                    ['icon' => '👤', 'bg' => 'bg-primary/10 dark:bg-primary/20', 'user' => 'Admin User', 'action' => 'created a new task', 'target' => '"Design mockups"', 'time' => '2 hours ago', 'type' => 'user'],
                    ['icon' => '🤖', 'bg' => 'bg-emerald-500/10 dark:bg-emerald-500/20', 'user' => 'AI Assistant', 'action' => 'generated project plan for', 'target' => 'Website Redesign', 'time' => '4 hours ago', 'type' => 'ai'],
                    ['icon' => '👥', 'bg' => 'bg-blue-500/10 dark:bg-blue-500/20', 'user' => 'System', 'action' => 'added new team member', 'target' => 'Sarah Johnson', 'time' => '6 hours ago', 'type' => 'system'],
                    ['icon' => '🔄', 'bg' => 'bg-violet-500/10 dark:bg-violet-500/20', 'user' => 'Admin User', 'action' => 'updated budget to', 'target' => '$20k allocated', 'time' => '8 hours ago', 'type' => 'project'],
                    ['icon' => '✨', 'bg' => 'bg-rose-500/10 dark:bg-rose-500/20', 'user' => 'System', 'action' => 'completed milestone for', 'target' => 'Team Onboarding', 'time' => '1 day ago', 'type' => 'system'],
                ] as $activity)
                <div class="group rounded-xl border border-muted/10 bg-background-secondary/50 p-4 transition-all duration-300 hover:border-primary/20 hover:bg-background-secondary dark:border-muted-dark/10 dark:bg-background-secondary-dark/50 dark:hover:border-primary/30 dark:hover:bg-background-secondary-dark">
                    <div class="flex items-start gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $activity['bg'] }} text-lg">
                            {{ $activity['icon'] }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-foreground dark:text-foreground-dark">
                                <span class="font-semibold">{{ $activity['user'] }}</span>
                                <span class="text-muted dark:text-muted-dark">{{ $activity['action'] }}</span>
                                <span class="font-semibold text-primary">{{ $activity['target'] }}</span>
                            </p>
                            <div class="mt-1 flex items-center gap-2">
                                <span class="inline-flex items-center rounded-full bg-muted/10 px-2 py-0.5 text-xs font-medium text-muted dark:bg-muted-dark/10 dark:text-muted-dark">{{ $activity['type'] }}</span>
                                <span class="text-xs text-muted dark:text-muted-dark">{{ $activity['time'] }}</span>
                            </div>
                        </div>
                        <button class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-muted opacity-0 group-hover:opacity-100 hover:bg-muted/10 hover:text-primary dark:text-muted-dark dark:hover:bg-muted-dark/10 dark:hover:text-primary transition-all">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m9 5 7 7-7 7"/><path d="M21 12H9"/></svg>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="rounded-2xl border border-muted/10 bg-background p-4 shadow-card sm:p-6 dark:border-muted-dark/10 dark:bg-background-dark">
            <h3 class="mb-5 text-lg font-bold text-foreground dark:text-foreground-dark">Quick Actions</h3>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('projects.create') }}" class="group flex flex-col items-center justify-center gap-2 rounded-xl border border-muted/10 bg-background-secondary px-4 py-4 text-center transition-all duration-300 hover:border-primary/30 hover:bg-primary/5 hover:scale-105 dark:border-muted-dark/10 dark:bg-background-secondary-dark dark:hover:bg-primary/10">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-primary/15 to-secondary/15 text-xl transition-transform duration-300 group-hover:scale-110 dark:from-primary/25 dark:to-secondary/25">📁</div>
                    <p class="text-sm font-semibold text-foreground dark:text-foreground-dark">Create Project</p>
                </a>
                <a href="{{ route('chat') }}" class="group flex flex-col items-center justify-center gap-2 rounded-xl border border-muted/10 bg-background-secondary px-4 py-4 text-center transition-all duration-300 hover:border-primary/30 hover:bg-primary/5 hover:scale-105 dark:border-muted-dark/10 dark:bg-background-secondary-dark dark:hover:bg-primary/10">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500/15 to-green-500/15 text-xl transition-transform duration-300 group-hover:scale-110 dark:from-emerald-500/25 dark:to-green-500/25">💬</div>
                    <p class="text-sm font-semibold text-foreground dark:text-foreground-dark">Open AI Chat</p>
                </a>
                <a href="{{ route('builder') }}" class="group flex flex-col items-center justify-center gap-2 rounded-xl border border-muted/10 bg-background-secondary px-4 py-4 text-center transition-all duration-300 hover:border-primary/30 hover:bg-primary/5 hover:scale-105 dark:border-muted-dark/10 dark:bg-background-secondary-dark dark:hover:bg-primary/10">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500/15 to-cyan-500/15 text-xl transition-transform duration-300 group-hover:scale-110 dark:from-blue-500/25 dark:to-cyan-500/25">🛠️</div>
                    <p class="text-sm font-semibold text-foreground dark:text-foreground-dark">Start Builder</p>
                </a>
                <a href="#" class="group flex flex-col items-center justify-center gap-2 rounded-xl border border-muted/10 bg-background-secondary px-4 py-4 text-center transition-all duration-300 hover:border-primary/30 hover:bg-primary/5 hover:scale-105 dark:border-muted-dark/10 dark:bg-background-secondary-dark dark:hover:bg-primary/10">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500/15 to-purple-500/15 text-xl transition-transform duration-300 group-hover:scale-110 dark:from-violet-500/25 dark:to-purple-500/25">📊</div>
                    <p class="text-sm font-semibold text-foreground dark:text-foreground-dark">View Reports</p>
                </a>
            </div>
        </div>
    </div>

    <!-- Chart.js Initialization -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const c = window.chartColors;
        const isMobile = window.matchMedia('(max-width: 640px)').matches;
        const tickSize = isMobile ? 10 : 11;
        const legendSize = isMobile ? 11 : 12;
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: { color: c.foreground, font: { family: 'Inter', size: legendSize } }
                }
            },
            scales: {
                x: {
                    grid: { color: c.grid },
                    ticks: { color: c.muted, maxRotation: 0, font: { family: 'Inter', size: tickSize } }
                },
                y: {
                    grid: { color: c.grid },
                    ticks: { color: c.muted, font: { family: 'Inter', size: tickSize } }
                }
            }
        };

        // Line Chart: Project Progress
        new Chart(document.getElementById('progressChart'), {
            type: 'line',
            data: {
                labels: ['Nov', 'Dec', 'Jan', 'Feb', 'Mar', 'Apr'],
                datasets: [{
                    label: 'Tasks Completed',
                    data: [28, 35, 42, 38, 55, 62],
                    borderColor: c.primary,
                    backgroundColor: 'rgba(124,58,237,0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointRadius: isMobile ? 3 : 5,
                    pointHoverRadius: isMobile ? 5 : 7,
                    pointBackgroundColor: c.primary,
                    pointBorderColor: c.background,
                    pointBorderWidth: 2,
                }, {
                    label: 'Tasks Created',
                    data: [32, 40, 48, 45, 60, 70],
                    borderColor: c.secondary,
                    backgroundColor: 'rgba(59,130,246,0.05)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointRadius: isMobile ? 3 : 5,
                    pointHoverRadius: isMobile ? 5 : 7,
                    pointBackgroundColor: c.secondary,
                    pointBorderColor: c.background,
                    pointBorderWidth: 2,
                }]
            },
            options: {
                ...commonOptions,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: { usePointStyle: true, boxWidth: isMobile ? 10 : 14, pointStyle: 'circle', color: c.foreground, font: { family: 'Inter', size: legendSize } }
                    },
                    tooltip: {
                        backgroundColor: c.background,
                        titleColor: c.foreground,
                        bodyColor: c.muted,
                        borderColor: c.grid,
                        borderWidth: 1,
                        padding: 12,
                        cornerRadius: 12,
                        displayColors: true,
                    }
                }
            }
        });

        // Pie Chart: Project Categories
        new Chart(document.getElementById('categoryChart'), {
            type: 'doughnut',
            data: {
                labels: ['Web Dev', 'Mobile', 'AI/ML', 'Design'],
                datasets: [{
                    data: [5, 3, 2, 2],
                    backgroundColor: [c.violet, c.secondary, c.emerald, c.amber],
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
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: c.background,
                        titleColor: c.foreground,
                        bodyColor: c.muted,
                        borderColor: c.grid,
                        borderWidth: 1,
                        padding: 12,
                        cornerRadius: 12,
                    }
                }
            }
        });

        // Bar Chart: Budget Overview
        new Chart(document.getElementById('budgetChart'), {
            type: 'bar',
            data: {
                labels: ['Web', 'Mobile', 'AI', 'Design', 'Marketing'],
                datasets: [{
                    label: 'Spent',
                    data: [4500, 3200, 2800, 1200, 700],
                    backgroundColor: [c.violet, c.secondary, c.emerald, c.amber, c.rose],
                    borderRadius: 8,
                    borderSkipped: false,
                }, {
                    label: 'Allocated',
                    data: [6000, 5000, 4000, 2500, 1500],
                    backgroundColor: 'rgba(148,163,184,0.15)',
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: { usePointStyle: true, boxWidth: isMobile ? 10 : 14, pointStyle: 'rectRounded', color: c.foreground, font: { family: 'Inter', size: legendSize } }
                    },
                    tooltip: {
                        backgroundColor: c.background,
                        titleColor: c.foreground,
                        bodyColor: c.muted,
                        borderColor: c.grid,
                        borderWidth: 1,
                        padding: 12,
                        cornerRadius: 12,
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: c.muted, maxRotation: 0, font: { family: 'Inter', size: tickSize } }
                    },
                    y: {
                        grid: { color: c.grid },
                        ticks: { color: c.muted, font: { family: 'Inter', size: tickSize }, callback: v => '$' + v }
                    }
                }
            }
        });
    });
    </script>
@endsection
