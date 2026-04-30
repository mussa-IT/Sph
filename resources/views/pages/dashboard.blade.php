@extends('layouts.app')

@section('title', 'Dashboard')

@php
    $totalProjects = App\Models\Project::count();
    $completedProjects = App\Models\Project::where('status', 'completed')->count();
    $totalChats = class_exists('App\Models\ChatSession') ? App\Models\ChatSession::count() : 0;
    $recentProjects = App\Models\Project::latest()->take(5)->get();
    $notifications = class_exists('App\Models\Notification') ? App\Models\Notification::latest()->take(5)->get() : collect();
@endphp

@section('content')
<div class="space-y-6">
    <!-- Welcome Section -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
            Welcome back, {{ auth()->user()->name }}!
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mb-6">
            Your project journey continues. Here's what's happening today.
        </p>
        <div class="flex flex-wrap gap-3">
            <button class="px-4 py-2 bg-gradient-to-r from-primary-600 to-primary-600 text-white rounded-lg hover:from-primary-700 hover:to-primary-700 transition-colors font-medium">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Project
            </button>
            <button class="px-4 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors font-medium">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
                Ask AI
            </button>
            <button class="px-4 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors font-medium">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0 0v1m0 0v1m-8 0h-8m-6 0a2 2 0 100 4 2 2 0 102 2h2a2 2 0 100 4 2 2 0 102 2z"></path>
                </svg>
                Upgrade Plan
            </button>
        </div>
    </div>

    <!-- Stats Cards (4 columns) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <x-stat-card 
            icon="📊" 
            title="Total Projects" 
            value="{{ $totalProjects }}" 
            change="+12%" 
            changeType="positive"
            color="primary"
        />
        
        <x-stat-card 
            icon="✅" 
            title="Completed Projects" 
            value="{{ $completedProjects }}" 
            change="+8%" 
            changeType="positive"
            color="success"
        />
        
        <x-stat-card 
            icon="💬" 
            title="AI Chats" 
            value="{{ $totalChats }}" 
            change="+24%" 
            changeType="positive"
            color="info"
        />
        
        <x-stat-card 
            icon="💰" 
            title="Revenue/Onchain" 
            value="$2.4k" 
            change="+15%" 
            changeType="positive"
            color="warning"
        />
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <x-card title="Weekly Activity">
            <canvas id="weeklyActivityChart" width="400" height="200"></canvas>
        </x-card>

        <x-card title="Project Completion">
            <canvas id="projectCompletionChart" width="400" height="200"></canvas>
        </x-card>
    </div>

    <!-- Recent Projects Table -->
    <x-card title="Recent Projects">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Progress</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Last Updated</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($recentProjects as $project)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $project->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-badge type="{{ $project->status ?? 'planning' }}">{{ ucfirst($project->status ?? 'Planning') }}</x-badge>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $project->progress ?? 25 }}%"></div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $project->updated_at->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-card>

    <!-- Widgets Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- AI Builder Widget -->
        <x-card title="AI Builder">
            <div class="space-y-4">
                <input type="text" placeholder="Describe your project idea..." class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                @if(Route::has('builder'))
                <a href="{{ route('builder') }}" class="block w-full bg-gradient-to-r from-primary-600 to-primary-600 text-white rounded-lg py-2 font-medium hover:from-primary-700 hover:to-primary-700 transition-colors text-center">
                @else
                <a href="#" class="block w-full bg-gradient-to-r from-primary-600 to-primary-600 text-white rounded-lg py-2 font-medium hover:from-primary-700 hover:to-primary-700 transition-colors text-center">
                @endif
                    Analyse Idea
                </a>
                
                @if($totalChats > 0)
                <div class="mt-4">
                    <p class="text-sm font-medium text-gray-900 dark:text-white mb-2">Recent AI Sessions</p>
                    <div class="space-y-2">
                        @for($i = 0; $i < min(3, $totalChats); $i++)
                        <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Session {{ $i + 1 }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ now()->subMinutes($i * 5)->diffForHumans() }} ago</span>
                        </div>
                        @endfor
                    </div>
                </div>
                @endif
            </div>
        </x-card>

        <!-- Onchain Activity Widget -->
        <x-card title="Onchain Activity">
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Connected Wallet</span>
                    <x-badge type="{{ auth()->user()->wallet_address ? 'success' : 'warning' }}">
                        {{ auth()->user()->wallet_address ? 'Connected' : 'Not Connected' }}
                    </x-badge>
                </div>
                @if(auth()->user()->wallet_address)
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ auth()->user()->wallet_address }}</div>
                @endif
                
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Badges Earned</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">8</span>
                </div>
                
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Recent Transactions</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">12</span>
                </div>
                
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Total Value</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">$45,230</span>
                </div>
            </div>
        </x-card>

        <!-- Notifications Panel -->
        <x-card title="Notifications">
            @if($notifications->count() > 0)
                <div class="space-y-3">
                    @foreach($notifications as $notification)
                        <div class="flex items-start space-x-3 p-3 hover:bg-gray-50 dark:hover:bg-gray-700 rounded cursor-pointer">
                            <div class="w-2 h-2 @if($notification->type == 'success') bg-green-500 @elseif($notification->type == 'warning') bg-yellow-500 @elseif($notification->type == 'error') bg-red-500 @else bg-blue-500 @endif rounded-full mt-1.5"></div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $notification->title }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $notification->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-gray-400 dark:text-gray-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 mt-2">No new notifications</p>
                </div>
            @endif
        </x-card>
    </div>
</div>

<script>
// Initialize Charts
document.addEventListener('DOMContentLoaded', function() {
    // Weekly Activity Chart
    const weeklyCtx = document.getElementById('weeklyActivityChart').getContext('2d');
    new Chart(weeklyCtx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Projects Created',
                data: [3, 5, 2, 8, 4, 6, 9],
                borderColor: 'rgb(124, 58, 237)',
                backgroundColor: 'rgba(124, 58, 237, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Project Completion Chart
    const completionCtx = document.getElementById('projectCompletionChart').getContext('2d');
    new Chart(completionCtx, {
        type: 'bar',
        data: {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            datasets: [{
                label: 'Completed',
                data: [3, 5, 2, 8],
                backgroundColor: 'rgba(34, 197, 94, 0.8)'
            }, {
                label: 'In Progress',
                data: [2, 3, 4, 3],
                backgroundColor: 'rgba(59, 130, 246, 0.8)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@endsection
