<?php

namespace App\Providers;

use Andriichello\Marketplace\Marketplace;
use App\Models\Banquet;
use App\Models\User;
use App\Nova\Dashboards\Main;
use App\Nova\Tools\BackupTool;
use App\Nova\Tools\MediaTool;
use App\Subscribers\NovaSubscriber;
use Badinansoft\LanguageSwitch\LanguageSwitch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Cards\Help;
use Laravel\Nova\Menu\Menu;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;

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

        Nova::serving(function () {
            Banquet::observe(NovaSubscriber::class);
        });

        Nova::footer(function () {
            return '<p class="mt-8 text-center text-xs text-80">'
                . '&copy; 2023 Imperia - By Andrii Prykhodko.'
                . '<br> <a class="link-default" href = "mailto: andriichello@gmail.com">andriichello@gmail.com</a>'
                . '</p>';
        });

        Nova::mainMenu(function (Request $request, Menu $menu) {
            $sections = [
                Marketplace::section($request),
                MenuSection::make(__('Offers'), [
                    MenuItem::resource(\App\Nova\Banquet::class),
                    MenuItem::resource(\App\Nova\Order::class),
                ])->icon('briefcase')->collapsable(),
                MenuSection::make(__('People'), [
                    MenuItem::resource(\App\Nova\User::class),
                    MenuItem::resource(\App\Nova\Customer::class),
                    MenuItem::resource(\App\Nova\FamilyMember::class),
                ])->icon('user')->collapsable(),
                MenuSection::make(__('Items'), [
                    MenuItem::resource(\App\Nova\Menu::class),
                    MenuItem::resource(\App\Nova\Product::class),
                    MenuItem::resource(\App\Nova\Ticket::class),
                    MenuItem::resource(\App\Nova\Space::class),
                    MenuItem::resource(\App\Nova\Service::class),
                ])->icon('collection')->collapsable(),
                MenuSection::make(__('Attachments'), [
                    MenuItem::resource(\App\Nova\Category::class),
                    MenuItem::resource(\App\Nova\Discount::class),
                    MenuItem::resource(\App\Nova\Comment::class),
                    MenuItem::resource(\App\Nova\Log::class),
                    MenuItem::resource(\App\Nova\Notification::class),
                ])->icon('paper-clip')->collapsable(),
                MenuSection::make(__('Restaurants'), [
                    MenuItem::resource(\App\Nova\Restaurant::class),
                    MenuItem::resource(\App\Nova\Schedule::class),
                    MenuItem::resource(\App\Nova\Holiday::class),
                    MenuItem::resource(\App\Nova\RestaurantReview::class),
                ])->icon('library')->collapsable(),
            ];

            $user = $request->user();

            if ($user instanceof User) {
                if ($user->isPreviewOnly()) {
                    $sections = [
                        MenuSection::make(__('nova.dashboard.restaurants'), [
                            MenuItem::resource(\App\Nova\Restaurant::class),
                            MenuItem::resource(\App\Nova\RestaurantReview::class),
                        ])->icon('library')->collapsable(),
                        MenuSection::make(__('nova.dashboard.items'), [
                            MenuItem::resource(\App\Nova\Menu::class),
                            MenuItem::resource(\App\Nova\Product::class),
                            MenuItem::resource(\App\Nova\Category::class),
                        ])->icon('collection')->collapsable(),
                        MenuSection::make(__('nova.dashboard.users'), [
                            MenuItem::resource(\App\Nova\User::class),
                        ])->icon('user')->collapsable(),
                    ];
                }
            }

            if ($request->user() && $request->user()->isAdmin()) {
                $sections[] = MediaTool::section($request, __('nova.dashboard.media'));
                // $sections[] = BackupTool::section($request);
            }

            return $sections;
        });
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
        Gate::define('viewNova', function (User $user) {
            return $user->isStaff() &&
                in_array($user->email, [
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
        return [
            new Main(),
        ];
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array
     */
    public function tools(): array
    {
        return [
            new Marketplace(),
            new MediaTool(__('nova.dashboard.media')),
            new BackupTool(),
            new LanguageSwitch(),
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
