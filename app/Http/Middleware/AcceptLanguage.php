<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

/**
 * Class AcceptLanguage.
 */
class AcceptLanguage
{
    /**
     * Changes app's locale if the corresponding header was specified.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $header = $request->headers->get('Accept-Language');
        $languages = Str::of($header)
            ->before(';')
            ->explode(',')
            ->all();

        foreach ($languages as $language) {
            if ($language === 'uk') {
                App::setLocale($language);
                break;
            }
        }

        return $next($request);
    }
}
