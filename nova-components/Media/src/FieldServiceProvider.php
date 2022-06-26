<?php

namespace Andriichello\Media;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Nova;

class FieldServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        Nova::serving(function () {
            Nova::script('media-field', __DIR__ . '/../dist/js/field.js');
        });
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
