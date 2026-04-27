<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirectToGoogle(Request $request)
    {
        if ($request->query('link') === 'true') {
            $request->session()->put('auth_google_link', true);
        } else {
            $request->session()->forget('auth_google_link');
        }

        return Socialite::driver('google')
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        $linkAccount = (bool) $request->session()->pull('auth_google_link', false);
        $googleUser = Socialite::driver('google')->stateless()->user();

        if (! filled($googleUser->email)) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Unable to retrieve your Google account email.']);
        }

        $existingUser = User::where('google_id', $googleUser->id)->first();

        if ($linkAccount && Auth::check()) {
            $currentUser = Auth::user();

            if ($currentUser->google_id === $googleUser->id) {
                return redirect()->route('settings')->with('status', 'google-connected');
            }

            if ($currentUser->email !== $googleUser->email) {
                return redirect()->route('settings')
                    ->withErrors(['google' => 'The Google account must use the same email address as your current account.']);
            }

            $currentUser->forceFill([
                'google_id' => $googleUser->id,
                'email_verified_at' => $currentUser->email_verified_at ?: now(),
            ])->save();

            return redirect()->route('settings')->with('status', 'google-connected');
        }

        if (! $existingUser) {
            $existingUser = User::where('email', $googleUser->email)->first();

            if ($existingUser) {
                $existingUser->forceFill([
                    'google_id' => $googleUser->id,
                    'email_verified_at' => $existingUser->email_verified_at ?: now(),
                ])->save();
            }
        }

        if (! $existingUser) {
            $existingUser = User::create([
                'name' => $googleUser->name ?: $googleUser->nickname ?: 'Google User',
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
                'email_verified_at' => now(),
                'password' => Hash::make(Str::random(24)),
            ]);
        }

        Auth::login($existingUser, true);

        return redirect()->intended(route('dashboard'));
    }
}
