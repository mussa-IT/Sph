@extends('layouts.app')

@section('title', 'Dashboard')

@php
    $pageTitle = 'Dashboard';
    $pageHeading = 'Overview';
@endphp

@push('styles')
<!-- Consolidated Professional Theme - Replaces all theme files -->
<link href="{{ asset('consolidated-theme.css') }}" rel="stylesheet">
<!-- CSS Variables extracted to resources/scss/variables.scss -->
<link href="{{ asset('resources/scss/variables.scss') }}" rel="stylesheet">
<!-- Card styles extracted to resources/scss/components/cards.scss -->
<link href="{{ asset('resources/scss/components/cards.scss') }}" rel="stylesheet">
<!-- Button styles extracted to resources/scss/components/buttons.scss -->
<link href="{{ asset('resources/scss/components/buttons.scss') }}" rel="stylesheet">
<!-- Badge and typography styles extracted to resources/scss/components/badges.scss -->
<link href="{{ asset('resources/scss/components/badges.scss') }}" rel="stylesheet">
<!-- Layout utilities extracted to resources/scss/layout/utilities.scss -->
<link href="{{ asset('resources/scss/layout/utilities.scss') }}" rel="stylesheet">
<style>
/* Original CSS variables, card, button, badge, and layout styles commented out for safety
:root {
    // All CSS variables moved to resources/scss/variables.scss
}

.premium-card {
    // All card styles moved to resources/scss/components/cards.scss
}

.btn {
    // All button styles moved to resources/scss/components/buttons.scss
}

.badge {
    // All badge styles moved to resources/scss/components/badges.scss
}

.container, .grid-premium {
    // All layout utilities moved to resources/scss/layout/utilities.scss
}
*/

/* Base Styles */
* {
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    line-height: 1.6;
    color: var(--gray-900);
    background: var(--gray-50);
}

.grid-cols-1 { grid-template-columns: repeat(1, minmax(0, 1fr)); }
.grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
.grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
.grid-cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }

/* Enhanced Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes scaleIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

@keyframes shimmer {
    0% {
        background-position: -200% 0;
    }
    100% {
        background-position: 200% 0;
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.animate-slide-in-left {
    animation: slideInLeft 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.animate-scale-in {
    animation: scaleIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Premium Input Styles */
.premium-input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid var(--neutral-300);
    border-radius: var(--radius-lg);
    font-size: 0.875rem;
    background: var(--bg-primary);
    transition: all 0.2s ease;
    outline: none;
}

.premium-input:focus {
    border-color: var(--primary-500);
    box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
}

/* Premium Table Styles */
.premium-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.premium-table th {
    text-align: left;
    padding: 0.75rem 1rem;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--neutral-500);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-bottom: 1px solid var(--neutral-200);
}

.premium-table td {
    padding: 1rem;
    border-bottom: 1px solid var(--neutral-100);
}

.premium-table tr:hover {
    background: var(--bg-tertiary);
}

/* Premium Progress Bar */
.progress-bar {
    width: 100%;
    height: 0.5rem;
    background: var(--neutral-200);
    border-radius: 9999px;
    overflow: hidden;
}

.progress-bar-fill {
    height: 100%;
    background: var(--gradient-primary);
    border-radius: 9999px;
    transition: width 0.6s ease;
}

/* Premium Stats Cards */
.stats-card {
    background: var(--bg-primary);
    border-radius: var(--radius-2xl);
    padding: var(--space-6);
    box-shadow: var(--shadow-card);
    border: 1px solid var(--neutral-200);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stats-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--gradient-primary);
}

.stats-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-elevation-2);
}

.stats-card-icon {
    width: 3rem;
    height: 3rem;
    border-radius: var(--radius-xl);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: var(--space-4);
}

.stats-card-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--neutral-900);
    margin-bottom: var(--space-1);
}

.stats-card-label {
    font-size: 0.875rem;
    color: var(--neutral-600);
    margin-bottom: var(--space-3);
}

.stats-card-change {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.stats-card-change.positive {
    background: var(--success-50);
    color: var(--success-700);
}

.stats-card-change.negative {
    background: #fef2f2;
    color: #dc2626;
}

/* Premium Chart Container */
.chart-container {
    background: var(--bg-primary);
    border-radius: var(--radius-2xl);
    padding: var(--space-6);
    box-shadow: var(--shadow-card);
    border: 1px solid var(--neutral-200);
    transition: all 0.3s ease;
}

.chart-container:hover {
    box-shadow: var(--shadow-elevation-2);
}

.chart-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: var(--space-6);
}

.chart-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--neutral-900);
}

.chart-subtitle {
    font-size: 0.875rem;
    color: var(--neutral-600);
    margin-top: var(--space-1);
}

.chart-canvas {
    height: 16rem;
    position: relative;
}

/* Badge Styles */
.badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    line-height: 1;
}

.badge-success {
    background: var(--success-50);
    color: var(--success-600);
}

.badge-warning {
    background: var(--warning-50);
    color: var(--warning-600);
}

.badge-error {
    background: var(--error-50);
    color: var(--error-600);
}

.badge-primary {
    background: var(--primary-50);
    color: var(--primary-600);
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.animate-slide-in-left {
    animation: slideInLeft 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Typography */
.text-display {
    font-size: 2.5rem;
    font-weight: 800;
    line-height: 1.2;
    letter-spacing: -0.025em;
}

.text-heading {
    font-size: 1.875rem;
    font-weight: 700;
    line-height: 1.3;
    letter-spacing: -0.025em;
}

.text-title {
    font-size: 1.25rem;
    font-weight: 600;
    line-height: 1.4;
}

.text-body {
    font-size: 0.875rem;
    font-weight: 400;
    line-height: 1.6;
}

.text-caption {
    font-size: 0.75rem;
    font-weight: 500;
    line-height: 1.4;
}

/* Layout Utilities */
.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 24px;
}

.grid-premium {
    display: grid;
    gap: 24px;
}

.grid-cols-1 { grid-template-columns: repeat(1, minmax(0, 1fr)); }
.grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
.grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
.grid-cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }

/* Responsive */
@media (min-width: 640px) {
    .container {
        padding: 0 32px;
    }
    
    .grid-cols-sm-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .grid-cols-sm-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
}

@media (min-width: 1024px) {
    .grid-cols-lg-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .grid-cols-lg-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
}

@media (min-width: 1280px) {
    .grid-cols-xl-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
}

/* Custom Scrollbar */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: var(--gray-100);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: var(--gray-400);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: var(--gray-500);
}

/* Loading States */
.skeleton {
    background: linear-gradient(90deg, var(--gray-200) 25%, var(--gray-100) 50%, var(--gray-200) 75%);
    background-size: 200% 100%;
    animation: skeleton 1.5s infinite;
}

@keyframes skeleton {
    0% {
        background-position: 200% 0;
    }
    100% {
        background-position: -200% 0;
    }
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
    :root {
        --gray-50: #111827;
        --gray-100: #1f2937;
        --gray-200: #374151;
        --gray-300: #4b5563;
        --gray-400: #6b7280;
        --gray-500: #9ca3af;
        --gray-600: #d1d5db;
        --gray-700: #e5e7eb;
        --gray-800: #f3f4f6;
        --gray-900: #f9fafb;
    }
    
    body {
        background: var(--gray-900);
        color: var(--gray-100);
    }
    
    .premium-card {
        background: var(--gray-800);
        border-color: var(--gray-700);
    }
    
    .btn-secondary {
        background: var(--gray-800);
        color: var(--gray-200);
        border-color: var(--gray-600);
    }
    
    .btn-secondary:hover {
        background: var(--gray-700);
        border-color: var(--gray-500);
    }
}
</style>
@endpush

@section('content')
<!-- Premium SaaS Dashboard Layout -->
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-xl border-b border-gray-200/50 shadow-sm">
        <div class="container">
            <div class="flex items-center justify-between h-16">
                <!-- Left Side - Logo & Search -->
                <div class="flex items-center gap-6">
                    <!-- Logo -->
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                            <span class="text-white font-bold text-lg">SP</span>
                        </div>
                        <div>
                            <span class="text-xl font-bold text-gray-900">Smart Project Hub</span>
                            <p class="text-xs text-gray-500">AI-Powered Platform</p>
                        </div>
                    </div>
                    
                    <!-- Search Bar -->
                    <div class="hidden lg:block">
                        <div class="relative">
                            <input 
                                type="text" 
                                placeholder="Search projects, tasks, or ask AI..."
                                class="w-96 pl-12 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all"
                            >
                            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <!-- Right Side - Actions -->
                <div class="flex items-center gap-3">
                    <!-- Quick Create Button -->
                    <button class="btn btn-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        New Project
                    </button>
                    
                    <!-- Notifications -->
                    <button class="btn btn-ghost relative">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <span class="absolute -top-1 -right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>
                    
                    <!-- Theme Toggle -->
                    <button class="btn btn-ghost">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                    </button>
                    
                    <!-- Wallet Connect -->
                    <button class="btn btn-secondary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                        Connect Wallet
                    </button>
                    
                    <!-- User Avatar Dropdown -->
                    <div class="relative">
                        <button class="flex items-center gap-3 p-2 rounded-xl hover:bg-gray-50 transition-colors">
                            <img src="https://picsum.photos/seed/user1/40/40.jpg" alt="User" class="w-10 h-10 rounded-full border-2 border-white shadow-sm">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Dashboard Content -->
    <main class="container py-8">
        <!-- Hero Section -->
        <section class="mb-12 animate-fade-in-up">
            <div class="premium-card p-8 bg-gradient-to-br from-blue-600 via-purple-600 to-indigo-700 text-white relative overflow-hidden">
                <!-- Background Pattern -->
                <div class="absolute inset-0 opacity-20">
                    <div class="absolute top-0 right-0 w-96 h-96 bg-white rounded-full -translate-y-48 translate-x-48"></div>
                    <div class="absolute bottom-0 left-0 w-72 h-72 bg-white rounded-full translate-y-36 -translate-x-36"></div>
                    <div class="absolute top-1/2 left-1/2 w-64 h-64 bg-gradient-to-br from-white/20 to-transparent rounded-full blur-3xl"></div>
                </div>
                
                <!-- Content -->
                <div class="relative z-10">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">
                        <div class="flex-1">
                            <div class="mb-6">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-white/20 text-white text-sm font-medium backdrop-blur-sm">
                                    Welcome back
                                </span>
                            </div>
                            <h1 class="text-4xl lg:text-5xl font-bold mb-4 leading-tight">
                                Hello, {{ Auth::user()->name ?? 'User' }}! 👋
                            </h1>
                            <p class="text-xl text-white/90 mb-8 max-w-2xl">
                                Your AI-powered workspace is ready. Let's build something amazing today.
                            </p>
                            <div class="flex flex-wrap gap-4">
                                <button class="btn bg-white text-blue-600 hover:bg-white/90 shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Create Project
                                </button>
                                <button class="btn bg-white/20 text-white hover:bg-white/30 backdrop-blur-sm border border-white/30">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                    Ask AI
                                </button>
                            </div>
                        </div>
                        
                        <!-- Quick Stats -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 lg:gap-8">
                            <div class="text-center bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20">
                                <div class="text-3xl lg:text-4xl font-bold mb-2">{{ $summary['total_projects'] ?? 12 }}</div>
                                <div class="text-sm text-white/80">Active Projects</div>
                            </div>
                            <div class="text-center bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20">
                                <div class="text-3xl lg:text-4xl font-bold mb-2">{{ $summary['completed_projects'] ?? 8 }}</div>
                                <div class="text-sm text-white/80">Completed</div>
                            </div>
                            <div class="text-center bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20">
                                <div class="text-3xl lg:text-4xl font-bold mb-2">{{ $summary['total_chat_sessions'] ?? 24 }}</div>
                                <div class="text-sm text-white/80">AI Chats</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Premium Stat Cards -->
        <section class="mb-12">
            <div class="grid-premium grid-cols-1 grid-cols-sm-2 grid-cols-lg-4">
                <!-- Total Projects Card -->
                <div class="stats-card animate-fade-in-up" style="animation-delay: 0.1s;">
                    <div class="stats-card-icon bg-gradient-to-br from-blue-500 to-blue-600 text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <div class="stats-card-value">{{ $summary['total_projects'] ?? 12 }}</div>
                    <div class="stats-card-label">Total Projects</div>
                    <div class="stats-card-change positive">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        +12%
                    </div>
                    <div class="mt-4">
                        <div class="flex items-center justify-between text-xs text-gray-500 mb-2">
                            <span>{{ $summary['completed_projects'] ?? 8 }} completed</span>
                            <span>{{ $summary['total_projects'] > 0 ? round(($summary['completed_projects'] / $summary['total_projects']) * 100) : 67 }}%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-bar-fill" style="width: {{ $summary['total_projects'] > 0 ? ($summary['completed_projects'] / $summary['total_projects'] * 100) : 67 }}%"></div>
                        </div>
                    </div>
                </div>

                <!-- Completed Projects Card -->
                <div class="premium-card p-6 animate-fade-in-up" style="animation-delay: 0.2s;">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <span class="badge badge-success">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            +8%
                        </span>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 mb-1">{{ $summary['completed_projects'] ?? 8 }}</div>
                    <div class="text-sm text-gray-500">Completed Projects</div>
                    <div class="mt-4 flex items-center gap-2 text-xs text-gray-500">
                        <span>On time delivery</span>
                        <span>•</span>
                        <span>92% success rate</span>
                    </div>
                    <div class="mt-2 w-full bg-gray-200 rounded-full h-1.5">
                        <div class="bg-green-600 h-1.5 rounded-full" style="width: 92%"></div>
                    </div>
                </div>

                <!-- AI Chats Used Card -->
                <div class="premium-card p-6 animate-fade-in-up" style="animation-delay: 0.3s;">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                        <span class="badge badge-primary">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            +25%
                        </span>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 mb-1">{{ $summary['total_chat_sessions'] ?? 24 }}</div>
                    <div class="text-sm text-gray-500">AI Chats Used</div>
                    <div class="mt-4 flex items-center gap-2 text-xs text-gray-500">
                        <span>{{ $summary['total_chat_messages'] ?? 156 }} messages</span>
                        <span>•</span>
                        <span>75% of limit</span>
                    </div>
                    <div class="mt-2 w-full bg-gray-200 rounded-full h-1.5">
                        <div class="bg-blue-600 h-1.5 rounded-full" style="width: 75%"></div>
                    </div>
                </div>

                <!-- Revenue/Credits Card -->
                <div class="premium-card p-6 animate-fade-in-up" style="animation-delay: 0.4s;">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <span class="badge badge-warning">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            +5%
                        </span>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 mb-1">$45,280</div>
                    <div class="text-sm text-gray-500">Total Revenue</div>
                    <div class="mt-4 flex items-center gap-2 text-xs text-gray-500">
                        <span>$32,750 earned</span>
                        <span>•</span>
                        <span>72% of goal</span>
                    </div>
                    <div class="mt-2 w-full bg-gray-200 rounded-full h-1.5">
                        <div class="bg-amber-600 h-1.5 rounded-full" style="width: 72%"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Beautiful Charts Section -->
        <section class="mb-8">
            <div class="grid-premium grid-cols-1 grid-cols-lg-2">
                <!-- Weekly Activity Chart -->
                <div class="premium-card p-6 animate-fade-in-up" style="animation-delay: 0.5s;">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-title font-semibold text-gray-900">Weekly Activity</h3>
                            <p class="text-body text-gray-500">Your project activity over the last week</p>
                        </div>
                        <div class="flex gap-2">
                            <button class="px-3 py-1 text-xs font-medium bg-purple-100 text-purple-700 rounded-lg">Week</button>
                            <button class="px-3 py-1 text-xs font-medium text-gray-500 hover:bg-gray-100 rounded-lg">Month</button>
                            <button class="px-3 py-1 text-xs font-medium text-gray-500 hover:bg-gray-100 rounded-lg">Year</button>
                        </div>
                    </div>
                    <div class="h-64">
                        <canvas id="weeklyActivityChart"></canvas>
                    </div>
                </div>

                <!-- Project Completion Trend -->
                <div class="premium-card p-6 animate-fade-in-up" style="animation-delay: 0.6s;">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-title font-semibold text-gray-900">Project Completion</h3>
                            <p class="text-body text-gray-500">Track your project completion trends</p>
                        </div>
                        <div class="badge badge-success">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            +15% vs last month
                        </div>
                    </div>
                    <div class="h-64">
                        <canvas id="projectCompletionChart"></canvas>
                    </div>
                </div>

                <!-- AI Usage Graph -->
                <div class="premium-card p-6 animate-fade-in-up" style="animation-delay: 0.7s;">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-title font-semibold text-gray-900">AI Usage</h3>
                            <p class="text-body text-gray-500">Track your AI assistant usage</p>
                        </div>
                        <div class="text-sm text-gray-500">
                            <span class="font-semibold">{{ $summary['total_chat_sessions'] ?? 24 }}</span> sessions this month
                        </div>
                    </div>
                    <div class="h-64">
                        <canvas id="aiUsageChart"></canvas>
                    </div>
                </div>

                <!-- Revenue Graph (Premium) -->
                <div class="premium-card p-6 animate-fade-in-up border-2 border-purple-200 relative" style="animation-delay: 0.8s;">
                    <div class="absolute top-4 right-4">
                        <div class="badge badge-primary">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                            Premium
                        </div>
                    </div>
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-title font-semibold text-gray-900">Revenue Analytics</h3>
                            <p class="text-body text-gray-500">Track your earnings and growth</p>
                        </div>
                        <div class="text-sm text-gray-500">
                            <span class="font-semibold">$45,280</span> total revenue
                        </div>
                    </div>
                    <div class="h-64">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </section>

        <!-- Recent Projects Table -->
        <section class="mb-8">
            <div class="premium-card p-6 animate-fade-in-up" style="animation-delay: 0.9s;">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
                    <div>
                        <h3 class="text-title font-semibold text-gray-900">Recent Projects</h3>
                        <p class="text-body text-gray-500">Manage and track all your projects</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <input 
                                type="text" 
                                placeholder="Search projects..."
                                class="pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20"
                            >
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <select class="px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-purple-500">
                            <option>All Status</option>
                            <option>Active</option>
                            <option>Completed</option>
                            <option>On Hold</option>
                        </select>
                        <a href="{{ route('projects.create') }}" class="btn btn-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            New Project
                        </a>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                                <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                                <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Budget</th>
                                <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @for($i = 1; $i <= 5; $i++)
                            <tr class="hover:bg-gray-50">
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <span class="text-blue-600 font-semibold text-sm">{{ substr(['Website Redesign', 'Mobile App', 'AI Platform', 'Marketing', 'Dashboard'][$i-1], 0, 2) }}</span>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ ['Website Redesign', 'Mobile App', 'AI Platform', 'Marketing', 'Dashboard'][$i-1] }}</p>
                                            <p class="text-sm text-gray-500">Updated {{ $i }}h ago</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="badge {{ ['bg-green-100 text-green-800', 'bg-blue-100 text-blue-800', 'bg-yellow-100 text-yellow-800', 'bg-green-100 text-green-800', 'bg-purple-100 text-purple-800'][$i-1] }}">
                                        {{ ['Active', 'In Progress', 'Planning', 'Completed', 'Review'][$i-1] }}
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-200 rounded-full h-2 w-20">
                                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ [75, 45, 30, 100, 60][$i-1] }}%"></div>
                                        </div>
                                        <span class="text-sm text-gray-600">{{ [75, 45, 30, 100, 60][$i-1] }}%</span>
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="text-sm font-medium text-gray-900">${{ number_format([15000, 8000, 12000, 25000, 5000][$i-1]) }}</span>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-2">
                                        <button class="p-1 text-gray-400 hover:text-gray-600" title="View">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </button>
                                        <button class="p-1 text-gray-400 hover:text-gray-600" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <button class="p-1 text-gray-400 hover:text-red-600" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="flex items-center justify-between mt-6">
                    <p class="text-sm text-gray-500">
                        Showing <span class="font-medium">1</span> to <span class="font-medium">5</span> of <span class="font-medium">12</span> results
                    </p>
                    <div class="flex items-center gap-2">
                        <button class="px-3 py-1 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Previous</button>
                        <button class="px-3 py-1 text-sm bg-purple-600 text-white rounded-lg">1</button>
                        <button class="px-3 py-1 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">2</button>
                        <button class="px-3 py-1 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">3</button>
                        <button class="px-3 py-1 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Next</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- AI Builder Widget -->
        <section class="mb-8">
            <div class="grid-premium grid-cols-1 grid-cols-lg-3">
                <!-- AI Builder -->
                <div class="premium-card p-6 animate-fade-in-up" style="animation-delay: 1.0s;">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-title font-semibold text-gray-900">AI Builder</h3>
                        <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                    </div>
                    <div class="mb-4">
                        <textarea 
                            placeholder="Ask AI to help you build something amazing..."
                            class="w-full h-32 p-3 bg-gray-50 border border-gray-200 rounded-xl text-sm resize-none focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20"
                        ></textarea>
                    </div>
                    <div class="flex gap-2">
                        <button class="btn btn-primary flex-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            Generate
                        </button>
                        <button class="btn btn-secondary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Continue
                        </button>
                    </div>
                    <div class="mt-4 space-y-2">
                        <p class="text-xs text-gray-500 font-medium">Recent AI Suggestions:</p>
                        <div class="space-y-1">
                            <div class="p-2 bg-purple-50 rounded-lg text-xs text-purple-700">Create a responsive landing page</div>
                            <div class="p-2 bg-blue-50 rounded-lg text-xs text-blue-700">Build a REST API with authentication</div>
                            <div class="p-2 bg-green-50 rounded-lg text-xs text-green-700">Design a database schema for e-commerce</div>
                        </div>
                    </div>
                </div>

                <!-- Onchain Widget -->
                <div class="premium-card p-6 animate-fade-in-up" style="animation-delay: 1.1s;">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-title font-semibold text-gray-900">Onchain Activity</h3>
                        <div class="badge badge-primary">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            Live
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Wallet Status</span>
                            <span class="badge badge-success">Connected</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Projects Minted</span>
                            <span class="text-sm font-medium text-gray-900">8</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Badges Earned</span>
                            <span class="text-sm font-medium text-gray-900">12</span>
                        </div>
                        <div class="border-t pt-4">
                            <p class="text-xs text-gray-500 font-medium mb-2">Recent Transactions</p>
                            <div class="space-y-2">
                                <div class="flex items-center gap-2 text-xs">
                                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                    <span class="text-gray-600">Project ownership verified</span>
                                    <span class="text-gray-400">2m ago</span>
                                </div>
                                <div class="flex items-center gap-2 text-xs">
                                    <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                    <span class="text-gray-600">Badge earned</span>
                                    <span class="text-gray-400">1h ago</span>
                                </div>
                                <div class="flex items-center gap-2 text-xs">
                                    <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                                    <span class="text-gray-600">Token transferred</span>
                                    <span class="text-gray-400">3h ago</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Team Section -->
                <div class="premium-card p-6 animate-fade-in-up" style="animation-delay: 1.2s;">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-title font-semibold text-gray-900">Team Workspace</h3>
                        <button class="text-sm text-purple-600 hover:text-purple-700 font-medium">View all</button>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <img src="https://picsum.photos/seed/team1/40/40.jpg" alt="Team member" class="w-10 h-10 rounded-full">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Sarah Chen</p>
                                <p class="text-xs text-gray-500">Lead Developer</p>
                            </div>
                            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                        </div>
                        <div class="flex items-center gap-3">
                            <img src="https://picsum.photos/seed/team2/40/40.jpg" alt="Team member" class="w-10 h-10 rounded-full">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Mike Johnson</p>
                                <p class="text-xs text-gray-500">UI Designer</p>
                            </div>
                            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                        </div>
                        <div class="flex items-center gap-3">
                            <img src="https://picsum.photos/seed/team3/40/40.jpg" alt="Team member" class="w-10 h-10 rounded-full">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Emily Davis</p>
                                <p class="text-xs text-gray-500">Project Manager</p>
                            </div>
                            <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                        </div>
                        <div class="border-t pt-4">
                            <p class="text-xs text-gray-500 font-medium mb-2">Shared Tasks</p>
                            <div class="space-y-1">
                                <div class="flex items-center gap-2 text-xs">
                                    <input type="checkbox" class="rounded border-gray-300">
                                    <span class="text-gray-600">Review API documentation</span>
                                </div>
                                <div class="flex items-center gap-2 text-xs">
                                    <input type="checkbox" class="rounded border-gray-300">
                                    <span class="text-gray-600">Update project timeline</span>
                                </div>
                                <div class="flex items-center gap-2 text-xs">
                                    <input type="checkbox" checked class="rounded border-gray-300">
                                    <span class="text-gray-600 line-through">Deploy to staging</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Notifications Panel -->
        <section class="mb-8">
            <div class="premium-card p-6 animate-fade-in-up" style="animation-delay: 1.3s;">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-title font-semibold text-gray-900">Notifications</h3>
                    <button class="text-sm text-purple-600 hover:text-purple-700 font-medium">Mark all as read</button>
                </div>
                <div class="space-y-3">
                    @for($i = 1; $i <= 4; $i++)
                    <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="w-10 h-10 {{ ['bg-blue-100', 'bg-green-100', 'bg-yellow-100', 'bg-purple-100'][$i-1] }} rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 {{ ['text-blue-600', 'text-green-600', 'text-yellow-600', 'text-purple-600'][$i-1] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">
                                {{ ['Project milestone completed', 'New team member joined', 'Budget approved', 'AI analysis ready'][$i-1] }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">{{ ['2 hours ago', '5 hours ago', '1 day ago', '2 days ago'][$i-1] }}</p>
                        </div>
                        <button class="p-1 text-gray-400 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    @endfor
                </div>
                <div class="mt-6 text-center">
                    <button class="text-sm text-purple-600 hover:text-purple-700 font-medium">View all notifications</button>
                </div>
            </div>
        </section>
    </main>
</div>

<!-- Premium Dashboard JavaScript -->
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Dashboard JavaScript Module (New) -->
<script src="{{ asset('resources/js/dashboard.js') }}"></script>

<!-- Original JavaScript kept as fallback for safety -->
<script>
/* 
// Original JavaScript commented out - moved to resources/js/dashboard.js
// If new module fails, uncomment this section as fallback
function initializeCharts() {
    // Chart.js premium color scheme
    const chartColors = {
        primary: '#9333ea',
        secondary: '#3b82f6',
        success: '#22c55e',
        warning: '#f59e0b',
        error: '#ef4444',
        info: '#06b6d4',
        gray: '#6b7280',
        light: '#f3f4f6',
        dark: '#1f2937'
    };

    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: 'rgba(255, 255, 255, 0.95)',
                titleColor: chartColors.dark,
                bodyColor: chartColors.gray,
                borderColor: chartColors.light,
                borderWidth: 1,
                padding: 12,
                cornerRadius: 8,
                displayColors: true,
                titleFont: { family: 'Inter', weight: '600', size: 12 },
                bodyFont: { family: 'Inter', size: 11 }
            }
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: { color: chartColors.gray, font: { family: 'Inter', size: 10 } }
            },
            y: {
                grid: { color: 'rgba(107, 114, 128, 0.1)', drawBorder: false },
                ticks: { color: chartColors.gray, font: { family: 'Inter', size: 10 } }
            }
        }
    };

    // Weekly Activity Chart
    const weeklyActivityCtx = document.getElementById('weeklyActivityChart');
    if (weeklyActivityCtx) {
        new Chart(weeklyActivityCtx, {
            type: 'bar',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Tasks Completed',
                    data: [12, 19, 15, 25, 22, 30, 18],
                    backgroundColor: 'rgba(147, 51, 234, 0.8)',
                    borderColor: 'rgba(147, 51, 234, 1)',
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                ...commonOptions,
                plugins: {
                    ...commonOptions.plugins,
                    legend: { display: false }
                },
                scales: {
                    ...commonOptions.scales,
                    y: {
                        ...commonOptions.scales.y,
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Project Completion Chart
    const projectCompletionCtx = document.getElementById('projectCompletionChart');
    if (projectCompletionCtx) {
        new Chart(projectCompletionCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Completed Projects',
                    data: [8, 12, 15, 18, 22, 25],
                    borderColor: chartColors.primary,
                    backgroundColor: 'rgba(147, 51, 234, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: chartColors.primary,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                ...commonOptions,
                interaction: { intersect: false, mode: 'index' },
                scales: {
                    ...commonOptions.scales,
                    y: {
                        ...commonOptions.scales.y,
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // AI Usage Chart
    const aiUsageCtx = document.getElementById('aiUsageChart');
    if (aiUsageCtx) {
        new Chart(aiUsageCtx, {
            type: 'doughnut',
            data: {
                labels: ['Code Generation', 'Data Analysis', 'Content Creation', 'Problem Solving'],
                datasets: [{
                    data: [35, 25, 20, 20],
                    backgroundColor: [
                        'rgba(147, 51, 234, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(245, 158, 11, 0.8)'
                    ],
                    borderColor: '#fff',
                    borderWidth: 2,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: { family: 'Inter', size: 11 },
                            color: chartColors.gray
                        }
                    },
                    tooltip: {
                        ...commonOptions.plugins.tooltip,
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed + '%';
                            }
                        }
                    }
                }
            }
        });
    }

    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Revenue',
                    data: [12000, 15000, 18000, 22000, 28000, 32000],
                    borderColor: 'rgba(59, 130, 246, 1)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: 'rgba(59, 130, 246, 1)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                ...commonOptions,
                interaction: { intersect: false, mode: 'index' },
                scales: {
                    ...commonOptions.scales,
                    y: {
                        ...commonOptions.scales.y,
                        beginAtZero: true,
                        ticks: {
                            ...commonOptions.scales.y.ticks,
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
}

function addInteractiveFeatures() {
    // Add smooth hover effects to cards
    const cards = document.querySelectorAll('.premium-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // Add click handlers for buttons
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            // Add ripple effect
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple');
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });

    // Add search functionality
    const searchInput = document.querySelector('input[placeholder="Search projects..."]');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    }

    // Add filter functionality
    const statusFilter = document.querySelector('select');
    if (statusFilter) {
        statusFilter.addEventListener('change', function(e) {
            const filterValue = e.target.value;
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                if (filterValue === 'All Status') {
                    row.style.display = '';
                } else {
                    const badge = row.querySelector('.badge');
                    const status = badge ? badge.textContent.trim() : '';
                    row.style.display = status.includes(filterValue) ? '' : 'none';
                }
            });
        });
    }
}

function initializeAnimations() {
    // Add staggered animation to elements
    const animatedElements = document.querySelectorAll('.animate-fade-in-up');
    animatedElements.forEach((element, index) => {
        element.style.animationDelay = `${index * 0.1}s`;
    });

    // Add intersection observer for scroll animations
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fade-in-up');
            }
        });
    });

    document.querySelectorAll('.premium-card').forEach(card => {
        observer.observe(card);
    });
}

// Add CSS for ripple effect
const style = document.createElement('style');
style.textContent = `
    .btn {
        position: relative;
        overflow: hidden;
    }
    
    .ripple {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.6);
        transform: scale(0);
        animation: ripple-animation 0.6s ease-out;
        pointer-events: none;
    }
    
    @keyframes ripple-animation {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    .premium-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .premium-card:hover {
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    }
    
    .badge {
        transition: all 0.2s ease;
    }
    
    .badge:hover {
        transform: scale(1.05);
    }
`;
document.head.appendChild(style);
*/
</script>
@endpush

@endsection
