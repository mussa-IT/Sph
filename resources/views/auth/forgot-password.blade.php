@extends('layouts.guest')

@section('title', 'Forgot Password')
@section('section', 'Authentication')
@section('heading', 'Reset your password')

@section('content')
    <div class="relative">
        <!-- Animated background gradient -->
        <div class="absolute -inset-4 rounded-3xl bg-gradient-to-br from-violet-600/10 via-blue-600/10 to-cyan-500/10 blur-2xl dark:from-violet-500/10 dark:via-blue-500/10 dark:to-cyan-400/10"></div>

        <div class="relative">
            <p class="mb-6 text-sm leading-relaxed text-muted dark:text-muted-dark">Enter your email address and we'll send you a secure link to reset your password.</p>

            <!-- Alerts -->
            @if ($errors->any())
                <x-auth-alert type="error" class="mb-5" />
            @endif

            @if (session('status'))
                <x-auth-alert type="success" class="mb-5">{{ session('status') }}</x-auth-alert>
            @endif

            <!-- Reset Form -->
            <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                @csrf

                <div class="space-y-2">
                    <label for="email" class="block text-sm font-semibold text-foreground dark:text-foreground-dark">{{ __app('email') }}</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="input-brand" />
                </div>

                <button type="submit" class="btn-brand-gradient touch-target w-full">
                    {{ __app('send_reset_link') }}
                </button>
            </form>

            <p class="mt-8 text-center text-sm text-muted dark:text-muted-dark">
                Remember your password?
                <a href="{{ route('login') }}" class="font-bold text-primary transition hover:text-primary/80 dark:text-primary dark:hover:text-primary/80">Back to sign in</a>
            </p>
        </div>
    </div>
@endsection
