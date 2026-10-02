<?php

namespace App\Providers;

use App\Helpers\ContentLocale;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\ServiceProvider;
use Spatie\Translatable\Facades\Translatable;

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
        // languages of restaurants are looked up once per request (or job)
        $this->app->scoped(ContentLocale::class);

        if (in_array($this->app->environment(), ['production', 'staging', 'dev'])) {
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
        // missing translations fall back to the app's fallback language, then to any other one
        Translatable::fallback(fallbackAny: true);
    }
}
