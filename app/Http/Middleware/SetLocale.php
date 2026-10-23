<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public const COOKIE = 'locale';

    public const COOKIE_MINUTES = 60 * 24 * 365;

    /**
     * Locale priority: the signed-in user's saved preference, then the browser cookie, then the
     * first supported locale (fr). Only allow-listed locales are ever applied.
     *
     * While signed in, the cookie is kept in step with the user's preference so that after logout
     * (which wipes the session) the user still sees their own language.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var list<string> $supported */
        $supported = config('app.supported_locales');

        $user = $request->user();
        $preferred = $user instanceof User ? $user->locale : null;
        $cookie = $request->cookie(self::COOKIE);

        $locale = match (true) {
            is_string($preferred) && in_array($preferred, $supported, true) => $preferred,
            is_string($cookie) && in_array($cookie, $supported, true) => $cookie,
            in_array(App::getLocale(), $supported, true) => App::getLocale(),
            default => $supported[0],
        };

        App::setLocale($locale);

        if ($preferred === $locale && $cookie !== $locale) {
            Cookie::queue(self::COOKIE, $locale, self::COOKIE_MINUTES);
        }

        return $next($request);
    }
}
