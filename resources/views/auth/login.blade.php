@extends('layouts.guest')

@section('title', 'Login')

@push('styles')
<style>
/* Premium Login Page Styles */
:root {
    --primary-50: #faf5ff;
    --primary-100: #f3e8ff;
    --primary-200: #e9d5ff;
    --primary-300: #d8b4fe;
    --primary-400: #c084fc;
    --primary-500: #a855f7;
    --primary-600: #9333ea;
    --primary-700: #7c3aed;
    --primary-800: #6b21a8;
    --primary-900: #581c87;
    
    --dark-blue-50: #eff6ff;
    --dark-blue-100: #dbeafe;
    --dark-blue-200: #bfdbfe;
    --dark-blue-300: #93c5fd;
    --dark-blue-400: #60a5fa;
    --dark-blue-500: #3b82f6;
    --dark-blue-600: #2563eb;
    --dark-blue-700: #1d4ed8;
    --dark-blue-800: #1e40af;
    --dark-blue-900: #1e3a8a;
    
    --gradient-primary: linear-gradient(135deg, var(--primary-600), var(--primary-800));
    --gradient-hero: linear-gradient(135deg, var(--primary-600), var(--dark-blue-600));
}

* {
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    line-height: 1.6;
    color: #1f2937;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
}

.login-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    position: relative;
    overflow: hidden;
}

.login-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 20% 80%, rgba(147, 51, 234, 0.3) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(59, 130, 246, 0.3) 0%, transparent 50%),
        radial-gradient(circle at 40% 40%, rgba(236, 72, 153, 0.2) 0%, transparent 50%);
    z-index: 1;
}

.login-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    box-shadow: 
        0 20px 25px -5px rgba(0, 0, 0, 0.1),
        0 8px 10px -6px rgba(0, 0, 0, 0.1),
        0 0 0 1px rgba(255, 255, 255, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
    padding: 3rem;
    width: 100%;
    max-width: 480px;
    position: relative;
    z-index: 2;
    animation: fadeInUp 0.6s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.logo-section {
    text-align: center;
    margin-bottom: 2rem;
}

.logo {
    width: 60px;
    height: 60px;
    background: var(--gradient-primary);
    border-radius: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
    box-shadow: 0 10px 15px -3px rgba(147, 51, 234, 0.3);
}

.logo-text {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--primary-600);
    margin-bottom: 0.5rem;
}

.logo-subtitle {
    color: #6b7280;
    font-size: 0.875rem;
}

.form-title {
    font-size: 1.875rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.5rem;
    text-align: center;
}

.form-subtitle {
    color: #6b7280;
    text-align: center;
    margin-bottom: 2rem;
    font-size: 0.875rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
}

.form-input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    font-size: 0.875rem;
    transition: all 0.2s ease;
    background: white;
}

.form-input:focus {
    outline: none;
    border-color: var(--primary-500);
    box-shadow: 0 0 0 3px rgba(147, 51, 234, 0.1);
}

.form-input::placeholder {
    color: #9ca3af;
}

.input-icon {
    position: relative;
}

.input-icon .form-input {
    padding-left: 3rem;
}

.input-icon::before {
    content: '';
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    width: 20px;
    height: 20px;
    background-size: contain;
    opacity: 0.5;
}

.input-icon.email::before {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.5a9 9 0 00-9 9m4.5-1.5H12'%3E%3C/path%3E%3C/svg%3E");
}

.input-icon.password::before {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'%3E%3C/path%3E%3C/svg%3E");
}

.form-options {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 2rem;
    font-size: 0.875rem;
}

.remember-me {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #6b7280;
}

.remember-me input[type="checkbox"] {
    border-radius: 4px;
    border-color: #d1d5db;
}

.forgot-link {
    color: var(--primary-600);
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s ease;
}

.forgot-link:hover {
    color: var(--primary-700);
}

.submit-btn {
    width: 100%;
    padding: 0.875rem;
    background: var(--gradient-primary);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 4px 6px -1px rgba(147, 51, 234, 0.3);
}

.submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(147, 51, 234, 0.4);
}

.submit-btn:active {
    transform: translateY(0);
}

.divider {
    display: flex;
    align-items: center;
    margin: 2rem 0;
    color: #9ca3af;
    font-size: 0.875rem;
}

.divider::before,
.divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #e5e7eb;
}

.divider span {
    padding: 0 1rem;
}

.social-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    width: 100%;
    padding: 0.875rem;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    color: #374151;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.social-btn:hover {
    background: #f9fafb;
    border-color: #d1d5db;
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.social-btn i {
    width: 20px;
    height: 20px;
}

.auth-links {
    text-align: center;
    margin-top: 2rem;
    color: #6b7280;
    font-size: 0.875rem;
}

.auth-links a {
    color: var(--primary-600);
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s ease;
}

.auth-links a:hover {
    color: var(--primary-700);
}

.alert {
    padding: 0.75rem 1rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    font-size: 0.875rem;
}

.alert-error {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.alert-success {
    background: #f0fdf4;
    color: #16a34a;
    border: 1px solid #bbf7d0;
}

/* Responsive Design */
@media (max-width: 640px) {
    .login-card {
        padding: 2rem;
        margin: 1rem;
    }
    
    .form-title {
        font-size: 1.5rem;
    }
    
    .form-options {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }
}
</style>
@endpush

@section('content')
<div class="login-container">
    <div class="login-card">
        <!-- Logo Section -->
        <div class="logo-section">
            <div class="logo">
                <span style="color: white; font-weight: bold; font-size: 1.5rem;">SP</span>
            </div>
            <h1 class="logo-text">Smart Project Hub</h1>
            <p class="logo-subtitle">AI-Powered Project Management</p>
        </div>

        <!-- Alerts -->
        @if ($errors->any())
            <div class="alert alert-error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        <!-- Login Form -->
        <form method="POST" action="{{ route('login') }}">
            @csrf

            <h2 class="form-title">Welcome back!</h2>
            <p class="form-subtitle">Enter your credentials to access your account</p>

            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-icon email">
                    <input 
                        id="email" 
                        name="email" 
                        type="email" 
                        value="{{ old('email') }}" 
                        required 
                        autofocus 
                        placeholder="you@example.com" 
                        class="form-input"
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <div class="input-icon password">
                    <input 
                        id="password" 
                        name="password" 
                        type="password" 
                        required 
                        placeholder="••••••••" 
                        class="form-input"
                    >
                </div>
            </div>

            <div class="form-options">
                <label class="remember-me">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <span>Remember me</span>
                </label>
                <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
            </div>

            <button type="submit" class="submit-btn">
                Sign In
            </button>
        </form>

        <!-- Divider -->
        <div class="divider">
            <span>Or continue with</span>
        </div>

        <!-- Social Login -->
        <a href="{{ route('auth.google.redirect') }}" class="social-btn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Continue with Google
        </a>

        <!-- Sign Up Link -->
        <div class="auth-links">
            Don't have an account? <a href="{{ route('register') }}">Create account</a>
        </div>
    </div>
</div>
@endsection
