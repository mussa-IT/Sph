@extends('layouts.app')

@section('title', 'Referral Expired')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-danger/5 via-background to-muted/5">
    <div class="container mx-auto px-4 py-16 max-w-md">
        <div class="surface-card interactive-lift p-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="text-4xl mb-4">⏰</div>
                <h1 class="text-2xl font-bold text-foreground dark:text-foreground-dark mb-4">
                    Referral Expired
                </h1>
                <p class="text-muted dark:text-muted-dark">
                    This referral code has expired or is no longer valid.
                </p>
            </div>

            <!-- Message -->
            <div class="p-4 rounded-xl bg-danger/10 border border-danger/20 mb-6">
                <p class="text-sm text-danger">
                    Referral codes typically expire after 30 days. Please contact the person who shared this code with you to get a new one.
                </p>
            </div>

            <!-- Actions -->
            <div class="space-y-4">
                <a href="{{ route('register') }}" class="w-full btn-brand text-center">
                    Sign Up Without Referral
                </a>
                
                <a href="{{ route('login') }}" class="w-full btn-brand-muted text-center">
                    Already Have an Account? Sign In
                </a>
            </div>

            <!-- Help -->
            <div class="mt-8 text-center">
                <p class="text-sm text-muted dark:text-muted-dark">
                    Need help? <a href="{{ route('contact') }}" class="text-primary hover:underline">Contact Support</a>
                </p>
            </div>
        </div>
    </div>
</div>
