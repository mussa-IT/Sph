@extends('layouts.guest')

@section('title', 'Register')
@section('section', 'Authentication')
@section('heading', 'Create your account!')
@section('subtitle', 'Join thousands of teams managing projects smarter with AI')

@section('content')
    <!-- Alerts -->
    @if ($errors->any())
        <div class="error-message mb-4">
            @foreach ($errors->all() as $error)
                {{ $error }}
            @endforeach
        </div>
    @endif

    <!-- Register Form -->
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="input-group">
            <label for="name">Full Name</label>
            <div class="input-field">
                <i class="far fa-user"></i>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus placeholder="John Doe" />
            </div>
        </div>

        <div class="input-group">
            <label for="email">Email</label>
            <div class="input-field">
                <i class="far fa-envelope"></i>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required placeholder="you@example.com" />
            </div>
        </div>

        <div class="input-group">
            <label for="password">Password</label>
            <div class="input-field">
                <i class="fas fa-lock"></i>
                <input id="password" name="password" type="password" required placeholder="••••••••" />
            </div>
        </div>

        <div class="input-group">
            <label for="password_confirmation">Confirm Password</label>
            <div class="input-field">
                <i class="fas fa-lock"></i>
                <input id="password_confirmation" name="password_confirmation" type="password" required placeholder="••••••••" />
            </div>
        </div>

        <button type="submit" class="auth-btn">Create Account</button>
    </form>

    <!-- Divider -->
    <div class="divider">
        <span>Or register with</span>
    </div>

    <!-- Social Signup -->
    <a href="{{ route('auth.google.redirect') }}" class="social-btn">
        <i class="fab fa-google"></i> Continue with Google
    </a>

    <div class="auth-links">
        Already have an account? <a href="{{ route('login') }}">Sign in</a>
    </div>
@endsection
