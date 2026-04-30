@extends('layouts.guest')

@section('title', 'Login')

@push('styles')
<style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Roboto, system-ui, -apple-system, sans-serif;
      background: linear-gradient(135deg, #4c1d95 0%, #2563eb 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1.5rem;
      margin: 0;
    }

    .auth-card {
      background: #ffffff;
      width: 100%;
      max-width: 440px;
      border-radius: 2rem;
      padding: 2.5rem 2rem 2.2rem;
      box-shadow: 0 30px 50px rgba(0, 0, 0, 0.25), 0 10px 25px rgba(79, 70, 229, 0.3);
      text-align: center;
    }

    .badge {
      display: inline-block;
      background: #eef2ff;
      color: #4f46e5;
      font-weight: 600;
      font-size: 0.8rem;
      letter-spacing: 0.5px;
      padding: 0.3rem 1rem;
      border-radius: 20px;
      margin-bottom: 1.5rem;
      text-transform: uppercase;
      border: 1px solid #e0e7ff;
    }

    h2 {
      font-size: 1.9rem;
      font-weight: 700;
      color: #1e1b4b;
      margin-bottom: 0.4rem;
    }

    .subtitle {
      color: #4b5563;
      font-size: 0.95rem;
      margin-bottom: 2rem;
      line-height: 1.5;
    }

    .input-group {
      text-align: left;
      margin-bottom: 1.5rem;
    }

    .input-group label {
      font-size: 0.85rem;
      font-weight: 600;
      color: #374151;
      display: block;
      margin-bottom: 0.3rem;
    }

    .input-field {
      display: flex;
      align-items: center;
      background: white;
      border: 1.5px solid #e5e7eb;
      border-radius: 14px;
      padding: 0.7rem 1rem;
      transition: 0.2s ease;
    }

    .input-field i {
      color: #9ca3af;
      font-size: 1.1rem;
      margin-right: 0.7rem;
    }

    .input-field input {
      border: none;
      background: transparent;
      width: 100%;
      font-size: 0.95rem;
      outline: none;
      color: #111827;
    }

    .input-field input::placeholder {
      color: #9ca3af;
      font-weight: 400;
    }

    .input-field:focus-within {
      border-color: #7c3aed;
      box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.15);
    }

    .options-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin: 0.2rem 0 1.8rem;
      font-size: 0.9rem;
    }

    .remember {
      display: flex;
      align-items: center;
      gap: 0.4rem;
      color: #4b5563;
      cursor: pointer;
    }

    .remember input[type="checkbox"] {
      accent-color: #7c3aed;
      width: 16px;
      height: 16px;
      margin: 0;
    }

    .forgot-link {
      color: #4f46e5;
      font-weight: 600;
      text-decoration: none;
      font-size: 0.9rem;
    }

    .forgot-link:hover {
      text-decoration: underline;
      color: #6d28d9;
    }

    .auth-btn {
      background: linear-gradient(115deg, #6d28d9, #4f46e5);
      border: none;
      color: white;
      font-weight: 700;
      font-size: 1rem;
      padding: 0.9rem;
      border-radius: 14px;
      width: 100%;
      cursor: pointer;
      transition: 0.25s;
      margin-bottom: 1.8rem;
      box-shadow: 0 8px 18px rgba(109, 40, 217, 0.35);
      letter-spacing: 0.3px;
    }

    .auth-btn:hover {
      background: linear-gradient(115deg, #5b21b6, #4338ca);
      transform: scale(1.01);
      box-shadow: 0 12px 22px rgba(109, 40, 217, 0.45);
    }

    .divider {
      display: flex;
      align-items: center;
      color: #9ca3af;
      font-size: 0.8rem;
      margin: 1.5rem 0 1.3rem;
    }

    .divider::before,
    .divider::after {
      content: "";
      flex: 1;
      height: 1px;
      background: #e5e7eb;
    }

    .divider span {
      margin: 0 0.8rem;
      font-weight: 500;
    }

    .social-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.6rem;
      background: #f9fafb;
      border: 1.5px solid #e5e7eb;
      border-radius: 14px;
      padding: 0.75rem;
      font-weight: 600;
      color: #374151;
      width: 100%;
      cursor: pointer;
      font-size: 0.95rem;
      transition: 0.2s;
    }

    .social-btn i {
      font-size: 1.2rem;
      color: #4f46e5;
    }

    .social-btn:hover {
      background: #ffffff;
      border-color: #c4b5fd;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.02);
    }

    .footer-note {
      margin-top: 1.8rem;
      font-size: 0.8rem;
      color: #6b7280;
      line-height: 1.5;
      padding: 0 0.3rem;
    }

    .switch-link {
      margin-top: 1.2rem;
      font-size: 0.9rem;
      color: #4b5563;
    }

    .switch-link a {
      color: #4f46e5;
      font-weight: 600;
      text-decoration: none;
    }

    .switch-link a:hover {
      text-decoration: underline;
      color: #6d28d9;
    }

    @media (max-width: 480px) {
      .auth-card {
        padding: 2rem 1.5rem;
      }
      h2 {
        font-size: 1.7rem;
      }
    }
</style>
@endpush

@section('content')
<div class="auth-card">
    <span class="badge">Smart Project Hub</span>
    <h2>Login to your account!</h2>
    <p class="subtitle">Enter your registered email address and password to login!</p>

    <!-- Alerts -->
    @if ($errors->any())
        <div style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; padding: 0.75rem 1rem; border-radius: 12px; margin-bottom: 1.5rem; font-size: 0.875rem;">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    @if (session('status'))
        <div style="background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; padding: 0.75rem 1rem; border-radius: 12px; margin-bottom: 1.5rem; font-size: 0.875rem;">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="input-group">
            <label>Email</label>
            <div class="input-field">
                <i class="far fa-envelope"></i>
                <input 
                    type="email" 
                    name="email" 
                    value="{{ old('email') }}" 
                    required 
                    autofocus 
                    placeholder="eg.pixelcot@gmail.com"
                >
            </div>
        </div>

        <div class="input-group">
            <label>Password</label>
            <div class="input-field">
                <i class="fas fa-lock"></i>
                <input 
                    type="password" 
                    name="password" 
                    required 
                    placeholder="***********"
                >
            </div>
        </div>

        <div class="options-row">
            <label class="remember">
                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Remember me
            </label>
            <a href="{{ route('password.request') }}" class="forgot-link">Forgot Password?</a>
        </div>

        <button type="submit" class="auth-btn">Login</button>
    </form>

    <div class="divider">
        <span>Or login with</span>
    </div>

    <a href="{{ route('auth.google.redirect') }}" class="social-btn">
        <i class="fab fa-google"></i> Continue with Google
    </a>

    <p class="footer-note">
        AI-powered project management for teams who want to build better, faster, and smarter.
    </p>

    <p class="switch-link">
        Don't have an account? <a href="{{ route('register') }}">Create one</a>
    </p>
</div>
@endsection
