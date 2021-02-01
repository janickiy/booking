<?php

namespace App\Http\Middleware;

use App;
use Closure;

class Locale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public function handle($request, Closure $next) {

        $raw_locale = $request->cookie('lang');

        if (in_array($raw_locale, config('app.locales'))) {
            $locale = $raw_locale;
        } else
            $locale = config('app.locale');

        App::setLocale($locale);

        return $next($request);
    }
}
