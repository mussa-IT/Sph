<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    /**
     * Switch the application language.
     *
     * @param  string  $locale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switch($locale)
    {
        // Validate the locale
        if (! in_array($locale, ['en', 'sw'])) {
            abort(400, 'Invalid locale');
        }

        // Set the locale in session
        Session::put('locale', $locale);

        if (Auth::check()) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $user->forceFill(['preferred_locale' => $locale])->save();
        }

        // Set the application locale
        App::setLocale($locale);

        // Redirect back
        return redirect()->back();
    }
}
