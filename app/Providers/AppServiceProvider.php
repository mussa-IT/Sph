<?php

namespace App\Providers;

use App\Services\AIService;
use App\Services\AIServiceInterface;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AIServiceInterface::class, AIService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.force_https')) {
            URL::forceScheme('https');
        }

        RateLimiter::for('ai-chat', function (Request $request) {
            $userKey = $request->user()?->id ?? $request->ip();
            $chatSessionRoute = $request->route('chatSession');
            $sessionKey = is_object($chatSessionRoute) && method_exists($chatSessionRoute, 'getKey')
                ? (string) $chatSessionRoute->getKey()
                : (string) $chatSessionRoute;
            if ($sessionKey === '') {
                $sessionKey = 'none';
            }

            return [
                Limit::perMinute((int) config('services.openai.rate_limits.chat_per_minute', 15))
                    ->by('ai-chat:minute:' . $userKey),
                Limit::perHour((int) config('services.openai.rate_limits.chat_per_hour', 120))
                    ->by('ai-chat:hour:' . $userKey),
                Limit::perMinute((int) config('services.openai.rate_limits.chat_per_session_per_minute', 10))
                    ->by('ai-chat:session:' . $userKey . ':' . $sessionKey),
            ];
        });

        RateLimiter::for('ai-builder', function (Request $request) {
            $userKey = $request->user()?->id ?? $request->ip();

            return [
                Limit::perMinute((int) config('services.openai.rate_limits.builder_per_minute', 12))
                    ->by('ai-builder:' . $userKey),
            ];
        });

        RateLimiter::for('auth-attempts', function (Request $request) {
            $identifier = strtolower((string) $request->input('email', $request->ip()));

            return Limit::perMinute((int) config('security.rate_limits.auth_per_minute', 10))
                ->by('auth:' . $identifier);
        });

        RateLimiter::for('sensitive-actions', function (Request $request) {
            $userKey = $request->user()?->id ? 'user:' . $request->user()->id : 'ip:' . $request->ip();

            return Limit::perMinute((int) config('security.rate_limits.sensitive_per_minute', 5))
                ->by('sensitive:' . $userKey);
        });

        RateLimiter::for('session-creation', function (Request $request) {
            $userKey = $request->user()?->id ?? $request->ip();

            return Limit::perMinute((int) config('security.rate_limits.session_creation_per_minute', 8))
                ->by('session-creation:' . $userKey);
        });

        // Set application locale from session, then user preference fallback.
        if (session()->has('locale')) {
            app()->setLocale((string) session('locale'));
        } elseif (Auth::check()) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            app()->setLocale((string) ($user->preferred_locale ?: config('app.locale')));
        }
    }
}
