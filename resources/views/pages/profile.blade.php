@extends('layouts.app')

@section('title', 'Profile')

@php
    $pageTitle = 'Profile';
    $pageHeading = 'Account';
    $initials = strtoupper(substr((string) $user->name, 0, 2));
@endphp

@section('content')
    <div class="space-y-6">
        <section class="overflow-hidden rounded-[2rem] border border-muted/20 bg-background-secondary shadow-card dark:border-muted-dark/20 dark:bg-background-secondary-dark">
            <div class="bg-gradient-to-r from-primary/15 via-secondary/10 to-emerald-400/10 px-6 py-8 sm:px-8">
                <p class="text-xs uppercase tracking-[.28em] text-muted dark:text-muted-dark">Premium Account Profile</p>
                <h2 class="mt-3 text-2xl font-semibold text-foreground dark:text-foreground-dark sm:text-3xl">{{ $user->name }}</h2>
                <p class="mt-2 text-sm text-muted dark:text-muted-dark">Manage your account identity and keep your workspace presence polished.</p>
            </div>

            <div class="grid gap-6 p-6 sm:p-8 lg:grid-cols-[280px_minmax(0,1fr)]">
                <aside class="rounded-3xl border border-muted/20 bg-background p-5 shadow-soft dark:border-muted-dark/20 dark:bg-background-dark">
                    @if(filled($user->avatar_url))
                        <img src="{{ $user->avatar_url }}" alt="Profile avatar" width="128" height="128" loading="lazy" decoding="async" class="mx-auto h-32 w-32 rounded-[2rem] object-cover shadow-lg shadow-primary/20" />
                    @else
                        <div class="mx-auto inline-flex h-32 w-32 items-center justify-center rounded-[2rem] bg-gradient-to-br from-primary to-secondary text-4xl font-semibold text-primary-foreground shadow-lg shadow-primary/30">
                            {{ $initials }}
                        </div>
                    @endif
                    <p class="mt-4 text-center text-sm font-semibold text-foreground dark:text-foreground-dark">Profile Photo</p>
                    <p class="mt-1 text-center text-xs text-muted dark:text-muted-dark">Auto-generated avatar</p>
                    <a href="{{ route('settings') }}" class="mt-5 inline-flex min-h-[44px] w-full items-center justify-center rounded-2xl border border-muted/20 bg-background-secondary px-4 py-2.5 text-sm font-semibold text-foreground transition hover:bg-muted/10 dark:border-muted-dark/20 dark:bg-background-secondary-dark dark:text-foreground-dark dark:hover:bg-muted-dark/10">
                        Update Photo Settings
                    </a>
                </aside>

                <div class="space-y-4">
                    <div class="rounded-2xl border border-muted/20 bg-background p-4 dark:border-muted-dark/20 dark:bg-background-dark">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="text-xs uppercase tracking-[.2em] text-muted dark:text-muted-dark">Account Insights</p>
                            <span class="text-xs font-medium text-muted dark:text-muted-dark">Live totals</span>
                        </div>
                        <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                            <div class="rounded-xl border border-muted/20 bg-background-secondary p-3 dark:border-muted-dark/20 dark:bg-background-secondary-dark">
                                <p class="text-[11px] uppercase tracking-[.16em] text-muted dark:text-muted-dark">Total projects</p>
                                <p class="mt-1 text-xl font-semibold text-foreground dark:text-foreground-dark">{{ $insights['total_projects'] }}</p>
                            </div>
                            <div class="rounded-xl border border-muted/20 bg-background-secondary p-3 dark:border-muted-dark/20 dark:bg-background-secondary-dark">
                                <p class="text-[11px] uppercase tracking-[.16em] text-muted dark:text-muted-dark">Completed projects</p>
                                <p class="mt-1 text-xl font-semibold text-foreground dark:text-foreground-dark">{{ $insights['completed_projects'] }}</p>
                            </div>
                            <div class="rounded-xl border border-muted/20 bg-background-secondary p-3 dark:border-muted-dark/20 dark:bg-background-secondary-dark">
                                <p class="text-[11px] uppercase tracking-[.16em] text-muted dark:text-muted-dark">AI chats used</p>
                                <p class="mt-1 text-xl font-semibold text-foreground dark:text-foreground-dark">{{ $insights['ai_chats_used'] }}</p>
                            </div>
                            <div class="rounded-xl border border-muted/20 bg-background-secondary p-3 dark:border-muted-dark/20 dark:bg-background-secondary-dark">
                                <p class="text-[11px] uppercase tracking-[.16em] text-muted dark:text-muted-dark">Tasks finished</p>
                                <p class="mt-1 text-xl font-semibold text-foreground dark:text-foreground-dark">{{ $insights['tasks_finished'] }}</p>
                            </div>
                            <div class="rounded-xl border border-muted/20 bg-background-secondary p-3 dark:border-muted-dark/20 dark:bg-background-secondary-dark">
                                <p class="text-[11px] uppercase tracking-[.16em] text-muted dark:text-muted-dark">Member since</p>
                                <p class="mt-1 text-sm font-semibold text-foreground dark:text-foreground-dark">{{ $insights['member_since'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl border border-muted/20 bg-background p-4 dark:border-muted-dark/20 dark:bg-background-dark">
                            <p class="text-xs uppercase tracking-[.2em] text-muted dark:text-muted-dark">Full Name</p>
                            <p class="mt-2 text-base font-semibold text-foreground dark:text-foreground-dark">{{ $user->name }}</p>
                        </div>
                        <div class="rounded-2xl border border-muted/20 bg-background p-4 dark:border-muted-dark/20 dark:bg-background-dark">
                            <p class="text-xs uppercase tracking-[.2em] text-muted dark:text-muted-dark">Email</p>
                            <p class="mt-2 break-all text-base font-semibold text-foreground dark:text-foreground-dark">{{ $user->email }}</p>
                        </div>
                        <div class="rounded-2xl border border-muted/20 bg-background p-4 dark:border-muted-dark/20 dark:bg-background-dark">
                            <p class="text-xs uppercase tracking-[.2em] text-muted dark:text-muted-dark">Joined Date</p>
                            <p class="mt-2 text-base font-semibold text-foreground dark:text-foreground-dark">{{ $joinedDate }}</p>
                        </div>
                        <div class="rounded-2xl border border-muted/20 bg-background p-4 dark:border-muted-dark/20 dark:bg-background-dark">
                            <p class="text-xs uppercase tracking-[.2em] text-muted dark:text-muted-dark">Account Badge</p>
                            <span class="mt-2 inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $badge['classes'] }}">
                                {{ $badge['label'] }}
                            </span>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl border border-muted/20 bg-background p-4 dark:border-muted-dark/20 dark:bg-background-dark">
                            <p class="text-xs uppercase tracking-[.2em] text-muted dark:text-muted-dark">Location</p>
                            <p class="mt-2 text-sm font-medium text-foreground dark:text-foreground-dark">{{ $user->location ?: 'Not provided' }}</p>
                        </div>
                        <div class="rounded-2xl border border-muted/20 bg-background p-4 dark:border-muted-dark/20 dark:bg-background-dark">
                            <p class="text-xs uppercase tracking-[.2em] text-muted dark:text-muted-dark">Website</p>
                            @if(filled($user->website))
                                <a href="{{ $user->website }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-block text-sm font-medium text-primary hover:underline">
                                    {{ $user->website }}
                                </a>
                            @else
                                <p class="mt-2 text-sm font-medium text-foreground dark:text-foreground-dark">Not provided</p>
                            @endif
                        </div>
                    </div>

                    <div class="rounded-2xl border border-muted/20 bg-background p-4 dark:border-muted-dark/20 dark:bg-background-dark">
                        <p class="text-xs uppercase tracking-[.2em] text-muted dark:text-muted-dark">Bio</p>
                        <p class="mt-2 whitespace-pre-line text-sm leading-7 text-foreground dark:text-foreground-dark">{{ $user->bio ?: 'No bio added yet.' }}</p>
                    </div>

                    <div class="pt-2">
                        <a href="{{ route('settings') }}" class="inline-flex min-h-[44px] items-center justify-center rounded-2xl bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90">
                            Edit Profile
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
