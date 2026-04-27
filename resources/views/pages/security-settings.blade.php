@extends('layouts.app')

@section('title', 'Security Settings')

@php
    $pageTitle = 'Security Settings';
    $pageHeading = 'Account Security';
@endphp

@section('content')
    <div class="space-y-6">
        @if (session('status') === 'password-updated')
            <div class="rounded-2xl border border-emerald-400/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">
                Password updated successfully.
            </div>
        @endif
        @if (session('status') === 'sessions-cleared')
            <div class="rounded-2xl border border-emerald-400/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">
                Other sessions logged out successfully. Removed {{ session('sessions_deleted_count', 0) }} session(s).
            </div>
        @endif
        @if (session('status') === 'two-factor-placeholder-updated')
            <div class="rounded-2xl border border-primary/30 bg-primary/10 px-4 py-3 text-sm text-primary">
                Two-factor placeholder preference saved. Full setup flow will be released soon.
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-danger/30 bg-danger/10 px-4 py-3 text-sm text-danger">
                We could not update your password. Please check the fields below.
            </div>
        @endif

        <section class="rounded-[2rem] border border-muted/20 bg-background-secondary p-6 shadow-card dark:border-muted-dark/20 dark:bg-background-secondary-dark">
            <div class="mb-6 flex flex-col gap-2">
                <p class="text-xs uppercase tracking-[.24em] text-muted dark:text-muted-dark">Security Center</p>
                <h2 class="text-2xl font-semibold text-foreground dark:text-foreground-dark">Change Password</h2>
                <p class="text-sm text-muted dark:text-muted-dark">Use your current password to authorize this change and choose a strong new password.</p>
            </div>

            <div class="grid gap-6 xl:grid-cols-[1fr_300px]">
                <form method="POST" action="{{ route('settings.password.update') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="current_password" class="mb-2 block text-sm font-medium text-foreground dark:text-foreground-dark">Current Password</label>
                        <input
                            id="current_password"
                            name="current_password"
                            type="password"
                            required
                            autocomplete="current-password"
                            class="w-full rounded-2xl border border-muted/20 bg-background px-4 py-3 text-sm text-foreground outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10 dark:border-muted-dark/20 dark:bg-background-dark dark:text-foreground-dark"
                        />
                        @error('current_password')
                            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-sm font-medium text-foreground dark:text-foreground-dark">New Password</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="new-password"
                            class="w-full rounded-2xl border border-muted/20 bg-background px-4 py-3 text-sm text-foreground outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10 dark:border-muted-dark/20 dark:bg-background-dark dark:text-foreground-dark"
                        />
                        @error('password')
                            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="mb-2 block text-sm font-medium text-foreground dark:text-foreground-dark">Confirm New Password</label>
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            required
                            autocomplete="new-password"
                            class="w-full rounded-2xl border border-muted/20 bg-background px-4 py-3 text-sm text-foreground outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10 dark:border-muted-dark/20 dark:bg-background-dark dark:text-foreground-dark"
                        />
                    </div>

                    <div class="pt-1">
                        <button type="submit" class="inline-flex min-h-[44px] items-center justify-center rounded-2xl bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90">
                            Update Password
                        </button>
                    </div>
                </form>

                <aside class="rounded-2xl border border-muted/20 bg-background p-4 dark:border-muted-dark/20 dark:bg-background-dark">
                    <p class="text-sm font-semibold text-foreground dark:text-foreground-dark">Strong Password Rules</p>
                    <ul class="mt-3 space-y-2 text-xs text-muted dark:text-muted-dark">
                        <li>At least 8 characters</li>
                        <li>Contains uppercase and lowercase letters</li>
                        <li>Contains at least one number</li>
                        <li>Contains at least one symbol</li>
                        <li>Must match confirmation field</li>
                    </ul>
                </aside>
            </div>
        </section>

        <section class="rounded-[2rem] border border-muted/20 bg-background-secondary p-6 shadow-card dark:border-muted-dark/20 dark:bg-background-secondary-dark">
            <div class="mb-5">
                <p class="text-xs uppercase tracking-[.24em] text-muted dark:text-muted-dark">Active Sessions</p>
                <h3 class="mt-2 text-lg font-semibold text-foreground dark:text-foreground-dark">Signed-in Devices</h3>
                <p class="mt-1 text-sm text-muted dark:text-muted-dark">Review sessions and revoke access on other devices.</p>
            </div>

            <div class="space-y-3">
                @forelse($sessions as $session)
                    <article class="rounded-2xl border border-muted/20 bg-background p-4 dark:border-muted-dark/20 dark:bg-background-dark">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-foreground dark:text-foreground-dark">
                                    {{ $session['device'] }} - {{ $session['browser'] }}
                                    @if($session['is_current'])
                                        <span class="ml-2 rounded-full bg-emerald-500/15 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 dark:text-emerald-300">Current Device</span>
                                    @endif
                                </p>
                                <p class="mt-1 text-xs text-muted dark:text-muted-dark">IP: {{ $session['ip_address'] ?: 'Unknown' }}</p>
                            </div>
                            <p class="text-xs text-muted dark:text-muted-dark">Last active: {{ $session['last_active_human'] }}</p>
                        </div>
                    </article>
                @empty
                    <p class="rounded-2xl border border-dashed border-muted/30 px-4 py-3 text-sm text-muted dark:border-muted-dark/30 dark:text-muted-dark">
                        No active session records were found.
                    </p>
                @endforelse
            </div>

            <form method="POST" action="{{ route('settings.security.sessions.logout-others') }}" class="mt-5 space-y-3">
                @csrf
                @method('PATCH')
                <div class="max-w-md">
                    <label for="sessions_current_password" class="mb-2 block text-sm font-medium text-foreground dark:text-foreground-dark">Confirm Current Password</label>
                    <input
                        id="sessions_current_password"
                        name="current_password"
                        type="password"
                        required
                        autocomplete="current-password"
                        class="w-full rounded-2xl border border-muted/20 bg-background px-4 py-3 text-sm text-foreground outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10 dark:border-muted-dark/20 dark:bg-background-dark dark:text-foreground-dark"
                    />
                    @error('current_password')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="inline-flex min-h-[44px] items-center justify-center rounded-2xl border border-warning/40 bg-warning/10 px-5 py-2.5 text-sm font-semibold text-warning transition hover:bg-warning/20">
                    Logout Other Sessions
                </button>
            </form>
        </section>

        <section class="rounded-[2rem] border border-muted/20 bg-background-secondary p-6 shadow-card dark:border-muted-dark/20 dark:bg-background-secondary-dark">
            <div class="mb-5">
                <p class="text-xs uppercase tracking-[.24em] text-muted dark:text-muted-dark">Two-Factor Authentication</p>
                <h3 class="mt-2 text-lg font-semibold text-foreground dark:text-foreground-dark">Future 2FA Architecture</h3>
                <p class="mt-1 text-sm text-muted dark:text-muted-dark">This placeholder stores your 2FA preferences now, ready for full verification flows later.</p>
            </div>

            <form method="POST" action="{{ route('settings.security.two-factor-placeholder.update') }}" class="grid gap-4 md:grid-cols-2">
                @csrf
                @method('PATCH')

                <div>
                    <label for="two_factor_channel" class="mb-2 block text-sm font-medium text-foreground dark:text-foreground-dark">Preferred 2FA Method</label>
                    <select
                        id="two_factor_channel"
                        name="two_factor_channel"
                        class="w-full rounded-2xl border border-muted/20 bg-background px-4 py-3 text-sm text-foreground outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10 dark:border-muted-dark/20 dark:bg-background-dark dark:text-foreground-dark"
                    >
                        <option value="authenticator" @selected(old('two_factor_channel', $twoFactor['channel']) === 'authenticator')>Authenticator App</option>
                        <option value="email" @selected(old('two_factor_channel', $twoFactor['channel']) === 'email')>Email Code</option>
                        <option value="sms" @selected(old('two_factor_channel', $twoFactor['channel']) === 'sms')>SMS Code</option>
                    </select>
                    @error('two_factor_channel')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-end">
                    <label class="flex items-center gap-3 rounded-2xl border border-muted/20 bg-background px-4 py-3 dark:border-muted-dark/20 dark:bg-background-dark">
                        <input
                            type="hidden"
                            name="two_factor_enabled"
                            value="0"
                        />
                        <input
                            type="checkbox"
                            name="two_factor_enabled"
                            value="1"
                            @checked(old('two_factor_enabled', $twoFactor['enabled']))
                            class="h-4 w-4 rounded border-muted/30 text-primary focus:ring-primary/40"
                        />
                        <span class="text-sm font-medium text-foreground dark:text-foreground-dark">Enable 2FA Placeholder</span>
                    </label>
                </div>

                <div class="md:col-span-2">
                    <button type="submit" class="inline-flex min-h-[44px] items-center justify-center rounded-2xl border border-primary/30 bg-primary/10 px-5 py-2.5 text-sm font-semibold text-primary transition hover:bg-primary/20">
                        Save 2FA Placeholder
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection
