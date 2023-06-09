<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

/**
 * Class HttpsProtocol.
 */
class HttpsProtocol
{
    /**
     * Enforce HTTPS in production and staging environments.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (!$request->secure() && in_array(App::environment(), ['production', 'staging'])) {
            return redirect()->secure($request->getRequestUri());
        }

        return $next($request);
    }
}
