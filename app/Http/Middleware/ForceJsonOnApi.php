<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Class ForceJsonOnApi.
 */
class ForceJsonOnApi
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (str_contains($request->url(), '/api/')) {
            $request->headers->set('Accept', 'application/json');
        }

        return $next($request);
    }
}
