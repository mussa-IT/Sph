<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\LogoutOtherSessionsRequest;
use App\Http\Requests\UpdateAccountPasswordRequest;
use App\Http\Requests\UpdateAccountPreferencesRequest;
use App\Http\Requests\UpdateAccountProfileRequest;
use App\Http\Requests\UpdateTwoFactorPlaceholderRequest;
use App\Services\AvatarImageService;
use App\Services\Security\SessionManagementService;
use App\Services\Security\TwoFactorSettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function __construct(
        private AvatarImageService $avatarImageService,
        private SessionManagementService $sessionManagementService,
        private TwoFactorSettingsService $twoFactorSettingsService
    )
    {
    }

    public function show(): View
    {
        return view('pages.settings', [
            'timezones' => $this->preferredTimezones(),
        ]);
    }

    public function security(): View
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $currentSessionId = session()->getId();

        return view('pages.security-settings', [
            'sessions' => $this->sessionManagementService->listUserSessions($user, $currentSessionId),
            'twoFactor' => $this->twoFactorSettingsService->placeholderState($user),
        ]);
    }

    public function updateProfile(UpdateAccountProfileRequest $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $validated = $request->validated();
        $emailChanged = $validated['email'] !== $user->email;
        $avatarPath = $user->avatar_path;

        if ($request->hasFile('avatar')) {
            $avatarPath = $this->avatarImageService->storeOptimized(
                file: $request->file('avatar'),
                userId: (int) $user->id,
                oldPath: $avatarPath
            );
        }

        $user->forceFill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'bio' => $validated['bio'] ?? null,
            'location' => $validated['location'] ?? null,
            'website' => $validated['website'] ?? null,
            'avatar_path' => $avatarPath,
            'email_verified_at' => $emailChanged ? null : $user->email_verified_at,
        ])->save();

        return back()->with('status', 'profile-updated');
    }

    public function updatePassword(UpdateAccountPasswordRequest $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $user->forceFill([
            'password' => Hash::make((string) $request->validated('password')),
        ])->save();

        return redirect()->route('settings.security')->with('status', 'password-updated');
    }

    public function logoutOtherSessions(LogoutOtherSessionsRequest $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $deletedCount = $this->sessionManagementService->logoutOtherSessions(
            user: $user,
            currentPassword: (string) $request->validated('current_password'),
            currentSessionId: $request->session()->getId()
        );

        return redirect()
            ->route('settings.security')
            ->with('status', 'sessions-cleared')
            ->with('sessions_deleted_count', $deletedCount);
    }

    public function updateTwoFactorPlaceholder(UpdateTwoFactorPlaceholderRequest $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $this->twoFactorSettingsService->updatePlaceholder($user, $request->validated());

        return redirect()->route('settings.security')->with('status', 'two-factor-placeholder-updated');
    }

    public function updatePreferences(UpdateAccountPreferencesRequest $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $validated = $request->validated();

        $user->forceFill([
            'preferred_locale' => $validated['preferred_locale'],
            'timezone' => $validated['timezone'],
            'theme_preference' => $validated['theme_preference'],
            'compact_mode' => (bool) ($validated['compact_mode'] ?? false),
            'comfortable_spacing' => (bool) ($validated['comfortable_spacing'] ?? false),
            'sidebar_collapsed_default' => (bool) ($validated['sidebar_collapsed_default'] ?? false),
            'receive_product_updates' => (bool) ($validated['receive_product_updates'] ?? false),
            'receive_marketing_emails' => (bool) ($validated['receive_marketing_emails'] ?? false),
        ])->save();

        $request->session()->put('locale', $validated['preferred_locale']);

        return back()->with('status', 'preferences-updated');
    }

    public function destroy(DeleteAccountRequest $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'account-deleted');
    }

    public function disconnectGoogle(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! filled($user->google_id)) {
            return back();
        }

        $user->forceFill(['google_id' => null])->save();

        return back()->with('status', 'google-disconnected');
    }

    /**
     * @return array<int, string>
     */
    private function preferredTimezones(): array
    {
        return Cache::rememberForever('settings:preferred_timezones', static function (): array {
            return [
                'America/Los_Angeles',
                'America/Denver',
                'America/Chicago',
                'America/New_York',
                'Europe/London',
                'Europe/Berlin',
                'Africa/Nairobi',
                'Asia/Dubai',
                'Asia/Singapore',
                'Asia/Tokyo',
                'Australia/Sydney',
                'UTC',
            ];
        });
    }
}
