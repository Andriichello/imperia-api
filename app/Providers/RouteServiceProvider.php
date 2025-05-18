<?php

namespace App\Providers;

use App\Http\Middleware\SetLocaleFromUrl;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Class RouteServiceProvider.
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));

            Route::middleware(['web', 'inertia'])
                ->namespace($this->namespace)
                ->prefix('/{locale}/inertia')
                ->name('inertia.')
                ->group(base_path('routes/inertia.php'));

            Route::fallback(function (Request $request) {
                if (Str::startsWith($request->path(), 'inertia')) {
                    $locale = $request->route('locale');
                    $resolvedLocale = SetLocaleFromUrl::getLocaleFromUrl($request);

                    if ($locale !== $resolvedLocale) {
                        return redirect('/' . $resolvedLocale . '/' . $request->path());
                    }
                }
            });
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
