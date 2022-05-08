<?php

namespace App\Providers;

use ClassicO\NovaMediaLibrary\NovaMediaLibrary;
use DigitalCreative\CollapsibleResourceManager\CollapsibleResourceManager;
use DigitalCreative\CollapsibleResourceManager\Resources\TopLevelResource;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Cards\Help;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;
use Spatie\BackupTool\BackupTool;

/**
 * Class NovaServiceProvider.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Register the Nova routes.
     *
     * @return void
     */
    protected function routes()
    {
        Nova::routes()
            ->withAuthenticationRoutes()
            ->withPasswordResetRoutes()
            ->register();
    }

    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewNova', function ($user) {
            return in_array($user->email, [
                //
            ]);
        });
    }

    /**
     * Get the cards that should be displayed on the default Nova dashboard.
     *
     * @return array
     */
    protected function cards(): array
    {
        return [
            new Help(),
        ];
    }

    /**
     * Get the extra dashboards that should be displayed on the Nova dashboard.
     *
     * @return array
     */
    protected function dashboards(): array
    {
        return [];
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array
     */
    public function tools(): array
    {
        return [
            new CollapsibleResourceManager([
                'navigation' => [
                    TopLevelResource::make([
                        'label' => 'Offers',
                        'expanded' => false,
                        'icon' => null,
                        'resources' => [
                            \App\Nova\Banquet::class,
                            \App\Nova\Order::class,
                        ]
                    ]),
                    TopLevelResource::make([
                        'label' => 'Items',
                        'expanded' => false,
                        'icon' => null,
                        'resources' => [
                            \App\Nova\Menu::class,
                            \App\Nova\Product::class,
                            \App\Nova\Ticket::class,
                            \App\Nova\Space::class,
                            \App\Nova\Service::class,
                        ]
                    ]),
                    TopLevelResource::make([
                        'label' => 'People',
                        'expanded' => false,
                        'icon' => null,
                        'resources' => [
                            \App\Nova\Customer::class,
                            \App\Nova\FamilyMember::class,
                        ]
                    ]),
                    TopLevelResource::make([
                        'label' => 'Attachments',
                        'expanded' => false,
                        'icon' => null,
                        'resources' => [
                            \App\Nova\Category::class,
                            \App\Nova\Discount::class,
                            \App\Nova\Comment::class,
                            \App\Nova\Log::class,
                        ]
                    ]),
                    TopLevelResource::make([
                        'label' => 'Management',
                        'expanded' => false,
                        'icon' => null,
                        'resources' => [
                            \App\Nova\User::class,
                            \App\Nova\Role::class,
                            \App\Nova\Notification::class,
                        ]
                    ]),
                ],
            ]),
            new NovaMediaLibrary(),
            new BackupTool(),
        ];
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
