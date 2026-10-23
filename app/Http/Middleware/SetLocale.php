<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Apply the locale chosen in the session, falling back to the app default (fr).
     * Only locales listed in config('app.supported_locales') are ever applied.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale');

        if (is_string($locale) && in_array($locale, config('app.supported_locales'), true)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
