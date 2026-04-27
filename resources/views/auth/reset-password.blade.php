@extends('layouts.guest')

@section('title', 'Reset Password')
@section('section', 'Authentication')
@section('heading', 'Choose a new password')

@section('content')
    <div class="relative">
        <!-- Animated background gradient -->
        <div class="absolute -inset-4 rounded-3xl bg-gradient-to-br from-violet-600/10 via-blue-600/10 to-cyan-500/10 blur-2xl dark:from-violet-500/10 dark:via-blue-500/10 dark:to-cyan-400/10"></div>

        <div class="relative">
            <p class="mb-6 text-sm leading-relaxed text-muted dark:text-muted-dark">Create a strong new password for your account.</p>

            <!-- Alerts -->
            @if ($errors->any())
                <x-auth-alert type="error" class="mb-5" />
            @endif

            <!-- Reset Form -->
            <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}" />

                <div class="space-y-2">
                    <label for="email" class="block text-sm font-semibold text-foreground dark:text-foreground-dark">{{ __app('email') }}</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required autofocus class="input-brand" />
                </div>

                <div class="space-y-2">
                    <label for="password" class="block text-sm font-semibold text-foreground dark:text-foreground-dark">{{ __app('new_password') }}</label>
                    <input id="password" name="password" type="password" required class="input-brand" />
                </div>

                <div class="space-y-2">
                    <label for="password_confirmation" class="block text-sm font-semibold text-foreground dark:text-foreground-dark">{{ __app('confirm_password') }}</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required class="input-brand" />
                </div>

                <button type="submit" class="btn-brand-gradient touch-target w-full">
                    {{ __app('reset_password') }}
                </button>
            </form>

            <p class="mt-8 text-center text-sm text-muted dark:text-muted-dark">
                <a href="{{ route('login') }}" class="font-bold text-primary transition hover:text-primary/80 dark:text-primary dark:hover:text-primary/80">Back to sign in</a>
            </p>
        </div>
    </div>
@endsection
