<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Apply the locale chosen in the session, falling back to the first supported locale (fr).
     * Only locales listed in config('app.supported_locales') are ever applied, including the
     * configured default, so a misconfigured APP_LOCALE cannot reach the frontend.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var list<string> $supported */
        $supported = config('app.supported_locales');
        $locale = $request->session()->get('locale');

        if (is_string($locale) && in_array($locale, $supported, true)) {
            App::setLocale($locale);
        } elseif (! in_array(App::getLocale(), $supported, true)) {
            App::setLocale($supported[0]);
        }

        return $next($request);
    }
}
