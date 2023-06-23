<?php

namespace App\Providers;

use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\ServiceProvider;

/**
 * Class AppServiceProvider.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (in_array($this->app->environment(), ['production', 'staging'])) {
            $this->app->afterResolving(
                \Illuminate\Contracts\Routing\UrlGenerator::class,
                function ($urlGenerator) {
                    /** @var UrlGenerator $urlGenerator */
                    $urlGenerator->forceScheme('https');
                }
            );
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
