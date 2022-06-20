<?php

namespace Andriichello\Marketplace;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Andriichello\Marketplace\Http\Middleware\Authorize;

/**
 * Class ToolServiceProvider.
 *
 * @property Application $app
 */
class ToolServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'marketplace');

        $this->app->booted(function () {
            $this->routes();
        });

        // Nova::serving(function (ServingNova $event) {
        //
        // });
    }

    /**
     * Register the tool's routes.
     *
     * @return void
     */
    protected function routes(): void
    {
        // @phpstan-ignore-next-line
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova', Authorize::class])
            ->prefix('nova-vendor/marketplace')
            ->group(__DIR__ . '/../routes/api.php');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }
}
