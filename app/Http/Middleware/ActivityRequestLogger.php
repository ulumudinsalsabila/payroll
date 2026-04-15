<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Support\Str;

class ActivityRequestLogger
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Log only GET requests for authenticated users
        if ($request->method() === 'GET' && auth()->check()) {
            try {
                $route = $request->route();
                $routeName = $route?->getName();
                $uri = $request->path();
                $query = $request->getQueryString();

                $module = $routeName
                    ? Str::upper(str_replace(['.', '/'], '_', $routeName))
                    : 'PAGE_VIEW';

                $desc = 'GET ' . $uri . ($query ? ('?' . $query) : '');

                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'VIEW',
                    'module' => $module,
                    'target_id' => null,
                    'description' => $desc,
                    'old_values' => null,
                    'new_values' => null,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            } catch (\Throwable $e) {
                // swallow logging errors to not break UX
            }
        }

        return $response;
    }
}
