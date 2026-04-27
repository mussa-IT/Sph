@extends('layouts.app')

@section('title', 'Settings')

@php
    $pageTitle = 'Settings';
    $pageHeading = 'Account Center';
    /** @var \App\Models\User $authUser */
    $authUser = auth()->user();
    $hasDeleteErrors = $errors->has('delete_password') || $errors->has('confirm_delete');
@endphp

@section('content')
    <div class="space-y-6">
        @if (session('status') === 'profile-updated')
            <div class="rounded-2xl border border-emerald-400/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">
                Profile updated successfully.
            </div>
        @endif
        @if (session('status') === 'password-updated')
            <div class="rounded-2xl border border-emerald-400/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">
                Password updated successfully.
            </div>
        @endif
        @if (session('status') === 'preferences-updated')
            <div class="rounded-2xl border border-emerald-400/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">
                Preferences saved successfully.
            </div>
        @endif
        @if (session('status') === 'google-disconnected')
            <div class="rounded-2xl border border-emerald-400/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">
                Google access has been disconnected.
            </div>
        @endif
        @if (session('status') === 'google-connected')
            <div class="rounded-2xl border border-emerald-400/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">
                Your Google account is now connected.
            </div>
        @endif

        <section class="rounded-[2rem] border border-muted/20 bg-background-secondary p-6 shadow-card dark:border-muted-dark/20 dark:bg-background-secondary-dark">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <p class="text-xs uppercase tracking-[.24em] text-muted dark:text-muted-dark">Workspace Identity</p>
                    <h2 class="mt-2 text-2xl font-semibold text-foreground dark:text-foreground-dark">Account Settings</h2>
                    <p class="mt-2 text-sm text-muted dark:text-muted-dark">Manage your identity, password and SaaS preferences with secure defaults.</p>
                </div>
                <div class="inline-flex items-center gap-3 rounded-2xl border border-muted/20 bg-background px-4 py-3 dark:border-muted-dark/20 dark:bg-background-dark">
                    @if(filled($authUser->avatar_url))
                        <img src="{{ $authUser->avatar_url }}" alt="Avatar" width="44" height="44" loading="lazy" decoding="async" class="h-11 w-11 rounded-xl object-cover" />
                    @else
                        <div class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-primary/10 text-sm font-semibold text-primary">
                            {{ strtoupper(substr((string) $authUser->name, 0, 2)) }}
                        </div>
                    @endif
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-foreground dark:text-foreground-dark">{{ $authUser->name }}</p>
                        <p class="truncate text-xs text-muted dark:text-muted-dark">{{ $authUser->email }}</p>
                    </div>
                </div>
            </div>
        </section>

        <div x-data="settingsTabs()" x-init="init()" class="grid gap-6 lg:grid-cols-[250px_minmax(0,1fr)]">
            <aside class="h-fit rounded-2xl border border-muted/20 bg-background-secondary p-3 shadow-card dark:border-muted-dark/20 dark:bg-background-secondary-dark">
                <p class="px-3 pb-2 pt-1 text-xs uppercase tracking-[.24em] text-muted dark:text-muted-dark">Tabs</p>
                <nav class="space-y-1">
                    <template x-for="tab in tabs" :key="tab.key">
                        <button
                            type="button"
                            @click="setTab(tab.key)"
                            class="flex min-h-[44px] w-full items-center justify-between rounded-xl px-3 py-2 text-left text-sm font-medium transition"
                            :class="activeTab === tab.key ? 'bg-primary text-primary-foreground shadow-soft' : 'text-foreground hover:bg-muted/10 dark:text-foreground-dark dark:hover:bg-muted-dark/10'"
                        >
                            <span x-text="tab.label"></span>
                            <span class="text-xs" x-text="tab.icon"></span>
                        </button>
                    </template>
                </nav>
            </aside>

            <div class="space-y-6">
                <section x-show="activeTab === 'profile'" class="rounded-[2rem] border border-muted/20 bg-background-secondary p-6 shadow-card dark:border-muted-dark/20 dark:bg-background-secondary-dark">
                    <div class="mb-5">
                        <p class="text-xs uppercase tracking-[.24em] text-muted dark:text-muted-dark">Profile</p>
                        <h3 class="mt-2 text-lg font-semibold text-foreground dark:text-foreground-dark">Personal Information</h3>
                        <p class="mt-1 text-sm text-muted dark:text-muted-dark">Keep your account name and email up to date.</p>
                    </div>

                    <form method="POST" action="{{ route('settings.profile.update') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        @method('PATCH')

                        <div>
                            <p class="mb-2 block text-sm font-medium text-foreground dark:text-foreground-dark">Profile Photo</p>
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                <div class="relative">
                                    <img
                                        id="avatar-live-preview"
                                        src="{{ $authUser->avatar_url }}"
                                        alt="Current avatar"
                                        width="64"
                                        height="64"
                                        loading="lazy"
                                        decoding="async"
                                        class="{{ filled($authUser->avatar_url) ? '' : 'hidden ' }}h-16 w-16 rounded-2xl object-cover border border-muted/20 dark:border-muted-dark/20"
                                    />
                                    <div
                                        id="avatar-initials-fallback"
                                        class="{{ filled($authUser->avatar_url) ? 'hidden ' : '' }}inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-primary/10 text-base font-semibold text-primary"
                                    >
                                        {{ strtoupper(substr((string) $authUser->name, 0, 2)) }}
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <input
                                        id="avatar"
                                        name="avatar"
                                        type="file"
                                        accept=".jpg,.jpeg,.png,.webp"
                                        class="w-full rounded-2xl border border-muted/20 bg-background px-4 py-3 text-sm text-foreground file:mr-3 file:rounded-xl file:border-0 file:bg-primary/10 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-primary outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10 dark:border-muted-dark/20 dark:bg-background-dark dark:text-foreground-dark"
                                    />
                                    <p class="mt-1 text-xs text-muted dark:text-muted-dark">JPG, PNG or WEBP up to 2MB.</p>
                                </div>
                            </div>
                            @error('avatar')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="name" class="mb-2 block text-sm font-medium text-foreground dark:text-foreground-dark">Full Name</label>
                            <input
                                id="name"
                                name="name"
                                type="text"
                                required
                                value="{{ old('name', $authUser->name) }}"
                                class="w-full rounded-2xl border border-muted/20 bg-background px-4 py-3 text-sm text-foreground outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10 dark:border-muted-dark/20 dark:bg-background-dark dark:text-foreground-dark"
                            />
                            @error('name')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="mb-2 block text-sm font-medium text-foreground dark:text-foreground-dark">Email Address</label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                required
                                value="{{ old('email', $authUser->email) }}"
                                class="w-full rounded-2xl border border-muted/20 bg-background px-4 py-3 text-sm text-foreground outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10 dark:border-muted-dark/20 dark:bg-background-dark dark:text-foreground-dark"
                            />
                            @error('email')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="bio" class="mb-2 block text-sm font-medium text-foreground dark:text-foreground-dark">Bio</label>
                            <textarea
                                id="bio"
                                name="bio"
                                rows="4"
                                placeholder="Tell people a little about yourself..."
                                class="w-full rounded-2xl border border-muted/20 bg-background px-4 py-3 text-sm text-foreground outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10 dark:border-muted-dark/20 dark:bg-background-dark dark:text-foreground-dark"
                            >{{ old('bio', $authUser->bio) }}</textarea>
                            @error('bio')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="location" class="mb-2 block text-sm font-medium text-foreground dark:text-foreground-dark">Location</label>
                                <input
                                    id="location"
                                    name="location"
                                    type="text"
                                    value="{{ old('location', $authUser->location) }}"
                                    placeholder="City, Country"
                                    class="w-full rounded-2xl border border-muted/20 bg-background px-4 py-3 text-sm text-foreground outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10 dark:border-muted-dark/20 dark:bg-background-dark dark:text-foreground-dark"
                                />
                                @error('location')
                                    <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="website" class="mb-2 block text-sm font-medium text-foreground dark:text-foreground-dark">Website</label>
                                <input
                                    id="website"
                                    name="website"
                                    type="url"
                                    value="{{ old('website', $authUser->website) }}"
                                    placeholder="https://your-site.com"
                                    class="w-full rounded-2xl border border-muted/20 bg-background px-4 py-3 text-sm text-foreground outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10 dark:border-muted-dark/20 dark:bg-background-dark dark:text-foreground-dark"
                                />
                                @error('website')
                                    <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="pt-1">
                            <button type="submit" class="inline-flex min-h-[44px] items-center justify-center rounded-2xl bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90">
                                Save Profile
                            </button>
                        </div>
                    </form>
                </section>

                <section x-show="activeTab === 'security'" class="rounded-[2rem] border border-muted/20 bg-background-secondary p-6 shadow-card dark:border-muted-dark/20 dark:bg-background-secondary-dark">
                    <div class="mb-5">
                        <p class="text-xs uppercase tracking-[.24em] text-muted dark:text-muted-dark">Security</p>
                        <h3 class="mt-2 text-lg font-semibold text-foreground dark:text-foreground-dark">Password</h3>
                        <p class="mt-1 text-sm text-muted dark:text-muted-dark">Use a strong password to keep your workspace secure.</p>
                    </div>

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
                                class="w-full rounded-2xl border border-muted/20 bg-background px-4 py-3 text-sm text-foreground outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10 dark:border-muted-dark/20 dark:bg-background-dark dark:text-foreground-dark"
                            />
                            @error('password')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="mb-2 block text-sm font-medium text-foreground dark:text-foreground-dark">Confirm Password</label>
                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                required
                                class="w-full rounded-2xl border border-muted/20 bg-background px-4 py-3 text-sm text-foreground outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10 dark:border-muted-dark/20 dark:bg-background-dark dark:text-foreground-dark"
                            />
                        </div>

                        <div class="pt-1">
                            <button type="submit" class="inline-flex min-h-[44px] items-center justify-center rounded-2xl bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90">
                                Update Password
                            </button>
                        </div>
                    </form>
                </section>

                <section x-show="activeTab === 'connected-accounts'" class="rounded-[2rem] border border-muted/20 bg-background-secondary p-6 shadow-card dark:border-muted-dark/20 dark:bg-background-secondary-dark">
                    <div class="mb-5">
                        <p class="text-xs uppercase tracking-[.24em] text-muted dark:text-muted-dark">Connected Accounts</p>
                        <h3 class="mt-2 text-lg font-semibold text-foreground dark:text-foreground-dark">Sign in with Google</h3>
                        <p class="mt-1 text-sm text-muted dark:text-muted-dark">Link your Google account for faster login and safer access.</p>
                        @error('google')
                            <div class="mt-4 rounded-2xl border border-danger/20 bg-danger/5 px-4 py-3 text-sm text-danger dark:border-danger/30 dark:bg-danger/10">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-3xl border border-muted/20 bg-background p-5 shadow-sm dark:border-muted-dark/20 dark:bg-background-dark">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-foreground dark:text-foreground-dark">Google</p>
                                    <p class="mt-1 text-sm text-muted dark:text-muted-dark">Use Google to sign in to your workspace.</p>
                                </div>
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
                                    {{ filled($authUser->google_id) ? 'Connected' : 'Not connected' }}
                                </span>
                            </div>

                            <div class="mt-5 space-y-3">
                                @if (filled($authUser->google_id))
                                    <p class="text-sm text-muted dark:text-muted-dark">Your Google account is linked. You can disconnect it anytime.</p>
                                    <form method="POST" action="{{ route('settings.account.google.disconnect') }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex min-h-[44px] items-center justify-center rounded-2xl bg-danger px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-danger/90">
                                            Disconnect Google
                                        </button>
                                    </form>
                                @else
                                    <p class="text-sm text-muted dark:text-muted-dark">Connect your Google account to simplify future sign-in.</p>
                                    <a href="{{ route('auth.google.redirect', ['link' => 'true']) }}" class="inline-flex min-h-[44px] items-center justify-center rounded-2xl bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90">
                                        Connect Google
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>

                <section x-show="activeTab === 'theme'" class="rounded-[2rem] border border-muted/20 bg-background-secondary p-6 shadow-card dark:border-muted-dark/20 dark:bg-background-secondary-dark">
                    <div class="mb-5">
                        <p class="text-xs uppercase tracking-[.24em] text-muted dark:text-muted-dark">Theme</p>
                        <h3 class="mt-2 text-lg font-semibold text-foreground dark:text-foreground-dark">Appearance</h3>
                        <p class="mt-1 text-sm text-muted dark:text-muted-dark">Control color mode and workspace density preferences.</p>
                    </div>

                    <form method="POST" action="{{ route('settings.preferences.update') }}" class="space-y-5">
                        @csrf
                        @method('PATCH')

                        <input type="hidden" name="preferred_locale" value="{{ old('preferred_locale', $authUser->preferred_locale) }}">
                        <input type="hidden" name="timezone" value="{{ old('timezone', $authUser->timezone) }}">
                        <input type="hidden" name="receive_product_updates" value="{{ old('receive_product_updates', $authUser->receive_product_updates ? 1 : 0) }}">
                        <input type="hidden" name="receive_marketing_emails" value="{{ old('receive_marketing_emails', $authUser->receive_marketing_emails ? 1 : 0) }}">

                        <div>
                            <label for="theme_preference" class="mb-2 block text-sm font-medium text-foreground dark:text-foreground-dark">Theme</label>
                            <select
                                id="theme_preference"
                                name="theme_preference"
                                class="w-full rounded-2xl border border-muted/20 bg-background px-4 py-3 text-sm text-foreground outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10 dark:border-muted-dark/20 dark:bg-background-dark dark:text-foreground-dark"
                            >
                                <option value="light" @selected(old('theme_preference', $authUser->theme_preference ?? 'system') === 'light')>Light</option>
                                <option value="dark" @selected(old('theme_preference', $authUser->theme_preference ?? 'system') === 'dark')>Dark</option>
                                <option value="system" @selected(old('theme_preference', $authUser->theme_preference ?? 'system') === 'system')>System Auto</option>
                            </select>
                            @error('theme_preference')
                                <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid gap-3 sm:grid-cols-3">
                            <label class="flex items-start gap-3 rounded-2xl border border-muted/20 bg-background px-4 py-3 dark:border-muted-dark/20 dark:bg-background-dark">
                                <input type="hidden" name="compact_mode" value="0" />
                                <input
                                    type="checkbox"
                                    name="compact_mode"
                                    value="1"
                                    @checked(old('compact_mode', $authUser->compact_mode))
                                    class="mt-1 h-4 w-4 rounded border-muted/30 text-primary focus:ring-primary/40"
                                />
                                <span>
                                    <span class="block text-sm font-medium text-foreground dark:text-foreground-dark">Compact Mode</span>
                                    <span class="block text-xs text-muted dark:text-muted-dark">Reduce spacing and padding.</span>
                                </span>
                            </label>

                            <label class="flex items-start gap-3 rounded-2xl border border-muted/20 bg-background px-4 py-3 dark:border-muted-dark/20 dark:bg-background-dark">
                                <input type="hidden" name="comfortable_spacing" value="0" />
                                <input
                                    type="checkbox"
                                    name="comfortable_spacing"
                                    value="1"
                                    @checked(old('comfortable_spacing', $authUser->comfortable_spacing))
                                    class="mt-1 h-4 w-4 rounded border-muted/30 text-primary focus:ring-primary/40"
                                />
                                <span>
                                    <span class="block text-sm font-medium text-foreground dark:text-foreground-dark">Comfortable Spacing</span>
                                    <span class="block text-xs text-muted dark:text-muted-dark">Increase spacing for readability.</span>
                                </span>
                            </label>

                            <label class="flex items-start gap-3 rounded-2xl border border-muted/20 bg-background px-4 py-3 dark:border-muted-dark/20 dark:bg-background-dark">
                                <input type="hidden" name="sidebar_collapsed_default" value="0" />
                                <input
                                    type="checkbox"
                                    name="sidebar_collapsed_default"
                                    value="1"
                                    @checked(old('sidebar_collapsed_default', $authUser->sidebar_collapsed_default))
                                    class="mt-1 h-4 w-4 rounded border-muted/30 text-primary focus:ring-primary/40"
                                />
                                <span>
                                    <span class="block text-sm font-medium text-foreground dark:text-foreground-dark">Sidebar Collapsed</span>
                                    <span class="block text-xs text-muted dark:text-muted-dark">Start with collapsed sidebar.</span>
                                </span>
                            </label>
                        </div>

                        <button type="submit" class="inline-flex min-h-[44px] items-center justify-center rounded-2xl bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90">
                            Save Theme
                        </button>
                    </form>
                </section>

                <section x-show="activeTab === 'language'" class="rounded-[2rem] border border-muted/20 bg-background-secondary p-6 shadow-card dark:border-muted-dark/20 dark:bg-background-secondary-dark">
                    <div class="mb-5">
                        <p class="text-xs uppercase tracking-[.24em] text-muted dark:text-muted-dark">Language</p>
                        <h3 class="mt-2 text-lg font-semibold text-foreground dark:text-foreground-dark">Locale & Region</h3>
                        <p class="mt-1 text-sm text-muted dark:text-muted-dark">Set language and timezone for your account.</p>
                    </div>

                    <form method="POST" action="{{ route('settings.preferences.update') }}" class="space-y-5">
                        @csrf
                        @method('PATCH')

                        <input type="hidden" name="theme_preference" value="{{ old('theme_preference', $authUser->theme_preference ?? 'system') }}">
                        <input type="hidden" name="compact_mode" value="{{ old('compact_mode', $authUser->compact_mode ? 1 : 0) }}">
                        <input type="hidden" name="comfortable_spacing" value="{{ old('comfortable_spacing', $authUser->comfortable_spacing ? 1 : 0) }}">
                        <input type="hidden" name="sidebar_collapsed_default" value="{{ old('sidebar_collapsed_default', $authUser->sidebar_collapsed_default ? 1 : 0) }}">
                        <input type="hidden" name="receive_product_updates" value="{{ old('receive_product_updates', $authUser->receive_product_updates ? 1 : 0) }}">
                        <input type="hidden" name="receive_marketing_emails" value="{{ old('receive_marketing_emails', $authUser->receive_marketing_emails ? 1 : 0) }}">

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label for="preferred_locale" class="mb-2 block text-sm font-medium text-foreground dark:text-foreground-dark">Language</label>
                                <select
                                    id="preferred_locale"
                                    name="preferred_locale"
                                    class="w-full rounded-2xl border border-muted/20 bg-background px-4 py-3 text-sm text-foreground outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10 dark:border-muted-dark/20 dark:bg-background-dark dark:text-foreground-dark"
                                >
                                    <option value="en" @selected(old('preferred_locale', $authUser->preferred_locale) === 'en')>English</option>
                                    <option value="sw" @selected(old('preferred_locale', $authUser->preferred_locale) === 'sw')>Swahili</option>
                                    <option value="fr" @selected(old('preferred_locale', $authUser->preferred_locale) === 'fr')>French</option>
                                    <option value="ar" @selected(old('preferred_locale', $authUser->preferred_locale) === 'ar')>Arabic</option>
                                </select>
                                @error('preferred_locale')
                                    <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="timezone" class="mb-2 block text-sm font-medium text-foreground dark:text-foreground-dark">Timezone</label>
                                <select
                                    id="timezone"
                                    name="timezone"
                                    class="w-full rounded-2xl border border-muted/20 bg-background px-4 py-3 text-sm text-foreground outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10 dark:border-muted-dark/20 dark:bg-background-dark dark:text-foreground-dark"
                                >
                                    @foreach($timezones as $timezone)
                                        <option value="{{ $timezone }}" @selected(old('timezone', $authUser->timezone) === $timezone)>{{ $timezone }}</option>
                                    @endforeach
                                </select>
                                @error('timezone')
                                    <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <button type="submit" class="inline-flex min-h-[44px] items-center justify-center rounded-2xl bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90">
                            Save Language
                        </button>
                    </form>
                </section>

                <section x-show="activeTab === 'notifications'" class="rounded-[2rem] border border-muted/20 bg-background-secondary p-6 shadow-card dark:border-muted-dark/20 dark:bg-background-secondary-dark">
                    <div class="mb-5">
                        <p class="text-xs uppercase tracking-[.24em] text-muted dark:text-muted-dark">Notifications</p>
                        <h3 class="mt-2 text-lg font-semibold text-foreground dark:text-foreground-dark">Email Preferences</h3>
                        <p class="mt-1 text-sm text-muted dark:text-muted-dark">Choose which updates you want to receive.</p>
                    </div>

                    <form method="POST" action="{{ route('settings.preferences.update') }}" class="space-y-5">
                        @csrf
                        @method('PATCH')

                        <input type="hidden" name="preferred_locale" value="{{ old('preferred_locale', $authUser->preferred_locale) }}">
                        <input type="hidden" name="timezone" value="{{ old('timezone', $authUser->timezone) }}">
                        <input type="hidden" name="theme_preference" value="{{ old('theme_preference', $authUser->theme_preference ?? 'system') }}">
                        <input type="hidden" name="compact_mode" value="{{ old('compact_mode', $authUser->compact_mode ? 1 : 0) }}">
                        <input type="hidden" name="comfortable_spacing" value="{{ old('comfortable_spacing', $authUser->comfortable_spacing ? 1 : 0) }}">
                        <input type="hidden" name="sidebar_collapsed_default" value="{{ old('sidebar_collapsed_default', $authUser->sidebar_collapsed_default ? 1 : 0) }}">

                        <div class="grid gap-3 sm:grid-cols-2">
                            <label class="flex items-start gap-3 rounded-2xl border border-muted/20 bg-background px-4 py-3 dark:border-muted-dark/20 dark:bg-background-dark">
                                <input type="hidden" name="receive_product_updates" value="0" />
                                <input
                                    type="checkbox"
                                    name="receive_product_updates"
                                    value="1"
                                    @checked(old('receive_product_updates', $authUser->receive_product_updates))
                                    class="mt-1 h-4 w-4 rounded border-muted/30 text-primary focus:ring-primary/40"
                                />
                                <span>
                                    <span class="block text-sm font-medium text-foreground dark:text-foreground-dark">Product Updates</span>
                                    <span class="block text-xs text-muted dark:text-muted-dark">Important feature updates and release notes.</span>
                                </span>
                            </label>

                            <label class="flex items-start gap-3 rounded-2xl border border-muted/20 bg-background px-4 py-3 dark:border-muted-dark/20 dark:bg-background-dark">
                                <input type="hidden" name="receive_marketing_emails" value="0" />
                                <input
                                    type="checkbox"
                                    name="receive_marketing_emails"
                                    value="1"
                                    @checked(old('receive_marketing_emails', $authUser->receive_marketing_emails))
                                    class="mt-1 h-4 w-4 rounded border-muted/30 text-primary focus:ring-primary/40"
                                />
                                <span>
                                    <span class="block text-sm font-medium text-foreground dark:text-foreground-dark">Marketing Emails</span>
                                    <span class="block text-xs text-muted dark:text-muted-dark">Tips, case studies and promotional content.</span>
                                </span>
                            </label>
                        </div>

                        <button type="submit" class="inline-flex min-h-[44px] items-center justify-center rounded-2xl bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90">
                            Save Notifications
                        </button>
                    </form>
                </section>

                <section x-data="{ deleteModalOpen: @js($hasDeleteErrors) }" x-show="activeTab === 'danger-zone'" class="rounded-[2rem] border border-danger/25 bg-danger/5 p-6 shadow-card">
                    <div class="mb-5">
                        <p class="text-xs uppercase tracking-[.24em] text-danger">Danger Zone</p>
                        <h3 class="mt-2 text-lg font-semibold text-foreground dark:text-foreground-dark">Delete Account</h3>
                        <p class="mt-1 text-sm text-muted dark:text-muted-dark">This permanently removes your account and related records. This action cannot be undone.</p>
                    </div>

                    <ul class="mb-5 list-disc space-y-1 pl-5 text-sm text-muted dark:text-muted-dark">
                        <li>All projects, tasks, budgets and notifications will be permanently removed.</li>
                        <li>You will be signed out from all active sessions.</li>
                        <li>This action cannot be reversed.</li>
                    </ul>

                    <button
                        type="button"
                        @click="deleteModalOpen = true"
                        class="inline-flex min-h-[44px] items-center justify-center rounded-2xl bg-danger px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-danger/90"
                    >
                        Delete My Account
                    </button>

                    <div
                        x-cloak
                        x-show="deleteModalOpen"
                        x-transition.opacity
                        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4 py-8"
                        @keydown.escape.window="deleteModalOpen = false"
                    >
                        <div
                            x-show="deleteModalOpen"
                            x-transition
                            @click.away="deleteModalOpen = false"
                            class="w-full max-w-lg rounded-2xl border border-danger/30 bg-background p-6 shadow-card dark:bg-background-dark"
                        >
                            <div class="mb-4">
                                <p class="text-xs uppercase tracking-[.24em] text-danger">Final Confirmation</p>
                                <h4 class="mt-2 text-lg font-semibold text-foreground dark:text-foreground-dark">Delete your account?</h4>
                                <p class="mt-2 text-sm text-muted dark:text-muted-dark">
                                    Enter your password and confirm that you understand this action is permanent.
                                </p>
                            </div>

                            <form method="POST" action="{{ route('settings.account.destroy') }}" class="space-y-4">
                                @csrf
                                @method('DELETE')

                                <div>
                                    <label for="delete_password" class="mb-2 block text-sm font-medium text-foreground dark:text-foreground-dark">Confirm Password</label>
                                    <input
                                        id="delete_password"
                                        name="delete_password"
                                        type="password"
                                        required
                                        autocomplete="current-password"
                                        class="w-full rounded-2xl border border-danger/30 bg-background px-4 py-3 text-sm text-foreground outline-none transition focus:border-danger focus:ring-4 focus:ring-danger/15 dark:bg-background-dark dark:text-foreground-dark"
                                    />
                                    @error('delete_password')
                                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <label class="flex items-center gap-3 text-sm text-foreground dark:text-foreground-dark">
                                    <input
                                        type="checkbox"
                                        name="confirm_delete"
                                        value="1"
                                        class="h-4 w-4 rounded border-danger/40 text-danger focus:ring-danger/40"
                                        required
                                    />
                                    I understand this action is permanent.
                                </label>
                                @error('confirm_delete')
                                    <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                                @enderror

                                <div class="flex items-center justify-end gap-3 pt-2">
                                    <button
                                        type="button"
                                        @click="deleteModalOpen = false"
                                        class="inline-flex min-h-[44px] items-center justify-center rounded-2xl border border-muted/20 bg-background-secondary px-4 py-2 text-sm font-semibold text-foreground transition hover:bg-muted/10 dark:border-muted-dark/20 dark:bg-background-secondary-dark dark:text-foreground-dark dark:hover:bg-muted-dark/10"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        class="inline-flex min-h-[44px] items-center justify-center rounded-2xl bg-danger px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-danger/90"
                                    >
                                        Permanently Delete
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    @once
        <script>
            function settingsTabs() {
                return {
                    activeTab: 'profile',
                    tabs: [
                        { key: 'profile', label: 'Profile', icon: '👤' },
                        { key: 'security', label: 'Security', icon: '🔒' },
                        { key: 'connected-accounts', label: 'Connected Accounts', icon: '🔗' },
                        { key: 'theme', label: 'Theme', icon: '🎨' },
                        { key: 'language', label: 'Language', icon: '🌐' },
                        { key: 'notifications', label: 'Notifications', icon: '🔔' },
                        { key: 'danger-zone', label: 'Danger Zone', icon: '⚠️' },
                    ],
                    init() {
                        if (@js($hasDeleteErrors)) {
                            this.activeTab = 'danger-zone';
                            return;
                        }

                        const hash = window.location.hash.replace('#', '');
                        if (this.tabs.some((tab) => tab.key === hash)) {
                            this.activeTab = hash;
                        }
                    },
                    setTab(tab) {
                        this.activeTab = tab;
                        window.history.replaceState({}, '', `#${tab}`);
                    }
                };
            }

            document.addEventListener('DOMContentLoaded', function () {
                const avatarInput = document.getElementById('avatar');
                const previewImage = document.getElementById('avatar-live-preview');
                const initialsFallback = document.getElementById('avatar-initials-fallback');

                if (!avatarInput || !previewImage || !initialsFallback) {
                    return;
                }

                avatarInput.addEventListener('change', function () {
                    const file = avatarInput.files && avatarInput.files[0] ? avatarInput.files[0] : null;
                    if (!file) {
                        return;
                    }

                    if (!file.type.startsWith('image/')) {
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function (event) {
                        if (!event.target || typeof event.target.result !== 'string') {
                            return;
                        }

                        previewImage.src = event.target.result;
                        previewImage.classList.remove('hidden');
                        initialsFallback.classList.add('hidden');
                    };
                    reader.readAsDataURL(file);
                });
            });
        </script>
    @endonce
@endsection
