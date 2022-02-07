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
     * Enforce HTTPS in production environment.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (!$request->secure() && App::environment() === 'production') {
            return redirect()->secure($request->getRequestUri());
        }

        return $next($request);
    }
}
