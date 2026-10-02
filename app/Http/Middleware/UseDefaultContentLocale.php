<?php

namespace App\Http\Middleware;

use App\Helpers\ContentLocale;
use Closure;
use Illuminate\Http\Request;

/**
 * Class UseDefaultContentLocale.
 *
 * The admin panel isn't translated, so restaurant content (titles, descriptions,
 * notes...) is shown and saved there in each restaurant's default language.
 * Other languages are written in the editor.
 */
class UseDefaultContentLocale
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        ContentLocale::instance()->useDefaults();

        return $next($request);
    }
}
