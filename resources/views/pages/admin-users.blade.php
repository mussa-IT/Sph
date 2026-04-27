@extends('layouts.app')

@section('title', 'Admin Users')

@php
    $pageTitle = 'Admin Users';
    $pageHeading = 'User directory and account controls';
@endphp

@section('content')
    <div class="space-y-6">
        <section class="rounded-2xl border border-muted/20 bg-background p-6 shadow-card dark:border-muted-dark/20 dark:bg-background-dark">
            <form action="{{ route('admin.users.index') }}" method="GET" class="grid gap-4">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-foreground dark:text-foreground-dark">Premium Admin Users</h2>
                        <p class="mt-1 text-sm text-muted dark:text-muted-dark">Search, filter, and manage suspended status for platform users.</p>
                    </div>

                    <div class="grid w-full gap-3 sm:w-auto sm:grid-cols-[1fr_auto]">
                        <input
                            type="text"
                            name="q"
                            value="{{ request('q') }}"
                            placeholder="Search by name or email"
                            class="w-full rounded-2xl border border-muted/20 bg-background px-4 py-3 text-sm text-foreground outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10 dark:border-muted-dark/20 dark:bg-background-dark dark:text-foreground-dark"
                        />
                        <button type="submit" class="rounded-2xl bg-primary px-4 py-3 text-sm font-semibold text-white transition hover:bg-primary-600">Search</button>
                    </div>
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-[.24em] text-muted dark:text-muted-dark">Role</span>
                        <select name="role" class="mt-2 w-full rounded-2xl border border-muted/20 bg-background px-4 py-3 text-sm text-foreground outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10 dark:border-muted-dark/20 dark:bg-background-dark dark:text-foreground-dark">
                            <option value="">All roles</option>
                            @foreach($roles as $key => $label)
                                <option value="{{ $key }}"{{ request('role') === $key ? ' selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-[.24em] text-muted dark:text-muted-dark">Status</span>
                        <select name="status" class="mt-2 w-full rounded-2xl border border-muted/20 bg-background px-4 py-3 text-sm text-foreground outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10 dark:border-muted-dark/20 dark:bg-background-dark dark:text-foreground-dark">
                            <option value="">All statuses</option>
                            <option value="active"{{ request('status') === 'active' ? ' selected' : '' }}>Active</option>
                            <option value="suspended"{{ request('status') === 'suspended' ? ' selected' : '' }}>Suspended</option>
                        </select>
                    </label>

                    <div class="flex items-end xl:col-span-2">
                        <button type="submit" class="w-full rounded-2xl bg-secondary px-4 py-3 text-sm font-semibold text-white transition hover:bg-secondary/90">Apply filters</button>
                    </div>
                </div>
            </form>
        </section>

        @if(session('success'))
            <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm text-emerald-700 dark:border-emerald-300/20 dark:bg-emerald-300/10 dark:text-emerald-200">
                {{ session('success') }}
            </div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-muted/20 bg-background shadow-card dark:border-muted-dark/20 dark:bg-background-dark">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-muted/20 text-left text-sm dark:divide-muted-dark/20">
                    <thead class="bg-muted/5 text-xs uppercase tracking-[.24em] text-muted dark:bg-muted-dark/10 dark:text-muted-dark">
                        <tr>
                            <th class="px-6 py-4">Name</th>
                            <th class="px-6 py-4">Email</th>
                            <th class="px-6 py-4">Joined</th>
                            <th class="px-6 py-4">Role</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-muted/20 dark:divide-muted-dark/20">
                        @forelse($users as $user)
                            <tr class="hover:bg-muted/5 dark:hover:bg-muted-dark/10">
                                <td class="px-6 py-4 text-foreground dark:text-foreground-dark">{{ $user->name }}</td>
                                <td class="px-6 py-4 text-muted dark:text-muted-dark">{{ $user->email }}</td>
                                <td class="px-6 py-4 text-muted dark:text-muted-dark">{{ $user->created_at->format('M j, Y') }}</td>
                                <td class="px-6 py-4 text-muted dark:text-muted-dark">{{ $roles[$user->role] ?? ucfirst($user->role) }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $user->suspended ? 'bg-rose-500/10 text-rose-600 dark:text-rose-300' : 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-300' }}">
                                        {{ $user->suspended ? 'Suspended' : 'Active' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $currentUser = auth()->user();
                                        $canSuspend = $currentUser && $currentUser->id !== $user->id && ! $user->isSuperAdmin() && ($currentUser->isSuperAdmin() || ! $user->isAdmin());
                                    @endphp

                                    @if($canSuspend)
                                        <form action="{{ route('admin.users.suspend', $user) }}" method="POST">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit" class="rounded-2xl bg-muted px-4 py-2 text-xs font-semibold uppercase tracking-[.16em] text-foreground transition hover:bg-muted/80 dark:text-foreground-dark">
                                                {{ $user->suspended ? 'Unsuspend' : 'Suspend' }}
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-muted dark:text-muted-dark">No actions</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-sm text-muted dark:text-muted-dark">No users match your filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-muted/10 bg-background-secondary px-6 py-4 dark:border-muted-dark/10 dark:bg-background-secondary-dark">
                {{ $users->links() }}
            </div>
        </section>
    </div>
@endsection
