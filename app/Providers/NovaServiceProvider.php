<?php

namespace App\Providers;

use Andriichello\Marketplace\Marketplace;
use Andriichello\Media\Media;
use App\Nova\Dashboards\Main;
use App\Nova\Tools\BackupTool;
use App\Nova\Tools\MediaTool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Cards\Help;
use Laravel\Nova\Menu\Menu;
use Laravel\Nova\Menu\MenuGroup;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Menu\MenuSeparator;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;
use Symfony\Component\Console\Output\ConsoleOutput;

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

        $tools = $this->tools();

        Nova::mainMenu(function (Request $request, Menu $menu) use ($tools) {
            $sections = [
                Marketplace::section($request),
                MenuSection::make('Offers', [
                    MenuItem::resource(\App\Nova\Banquet::class),
                    MenuItem::resource(\App\Nova\Order::class),
                ])->icon('briefcase')->collapsable(),
                MenuSection::make('People', [
                    MenuItem::resource(\App\Nova\Customer::class),
                    MenuItem::resource(\App\Nova\FamilyMember::class),
                ])->icon('user')->collapsable(),
                MenuSection::make('Items', [
                    MenuItem::resource(\App\Nova\Menu::class),
                    MenuItem::resource(\App\Nova\Product::class),
                    MenuItem::resource(\App\Nova\Ticket::class),
                    MenuItem::resource(\App\Nova\Space::class),
                    MenuItem::resource(\App\Nova\Service::class),
                ])->icon('collection')->collapsable(),
                MenuSection::make('Attachments', [
                    MenuItem::resource(\App\Nova\Category::class),
                    MenuItem::resource(\App\Nova\Discount::class),
                    MenuItem::resource(\App\Nova\Comment::class),
                    MenuItem::resource(\App\Nova\Log::class),
                    MenuItem::resource(\App\Nova\Notification::class),
                ])->icon('paper-clip')->collapsable(),
            ];

            if ($request->user() && $request->user()->isStaff()) {
                $sections[] = MediaTool::section($request);
                $sections[] = BackupTool::section($request);
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
            new MediaTool(),
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
