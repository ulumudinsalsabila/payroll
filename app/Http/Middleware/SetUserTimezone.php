<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetUserTimezone
{
    public function handle(Request $request, Closure $next)
    {
        $timezone = null;

        if (auth()->check()) {
            $timezone = auth()->user()->timezone;
        }

        if (!$timezone) {
            $timezone = $request->session()->get('timezone');
        }

        if (!$timezone) {
            $timezone = config('app.timezone');
        }

        if ($timezone && in_array($timezone, timezone_identifiers_list(), true)) {
            date_default_timezone_set($timezone);
            config(['app.timezone' => $timezone]);
        }

        return $next($request);
    }
}
