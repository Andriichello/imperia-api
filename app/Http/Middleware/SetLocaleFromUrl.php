<?php

namespace App\Http\Middleware;

use App\Models\Restaurant;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

/**
 * Class SetLocaleFromUrl.
 */
class SetLocaleFromUrl
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response|RedirectResponse) $next
     *
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $locale = $request->route('locale');
        $resolvedLocale = static::getLocaleFromUrl($request);

        if ($locale !== $resolvedLocale) {
            // Optionally redirect to the default locale if not supported
            $url = Str::of($request->path())
                ->after('/inertia')
                ->start('/' . $resolvedLocale . '/inertia')
                ->value();

            return redirect($url);
        }

        App::setLocale($locale);

        return $next($request);
    }

    /**
     * Get locale from url.
     *
     * @param Request $request
     *
     * @return string
     */
    public static function getLocaleFromUrl(Request $request): string
    {
        $locale = $request->route('locale');
        $defaultLocale = config('app.locale');

        $restaurantId = $request->route('restaurant_id');

        if ($restaurantId) {
            $restaurant = Restaurant::query()
                ->find($restaurantId);

            $restaurantLocale = $restaurant?->locale;
        }

        if (!in_array($locale, config('app.supported_locales'))) {
            $restaurantLocale = $restaurantLocale ?? null;

            return in_array($restaurantLocale, config('app.supported_locales'))
                ? $restaurantLocale : $defaultLocale;
        }

        return $locale;
    }
}
