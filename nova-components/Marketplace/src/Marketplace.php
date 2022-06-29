<?php

namespace Andriichello\Marketplace;

use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool;

class Marketplace extends Tool
{
    /**
     * Perform any tasks that need to happen when the tool is booted.
     *
     * @return void
     */
    public function boot(): void
    {
        Nova::script('marketplace', __DIR__ . '/../dist/js/tool.js');
        Nova::style('marketplace', __DIR__ . '/../dist/css/tool.css');
    }

    /**
     * Build the menu that renders the navigation links for the tool.
     *
     * @param Request $request
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function menu(Request $request): mixed
    {
        return self::section($request);
    }

    /**
     * Build the menu that renders the navigation links for the tool.
     *
     * @param Request $request
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public static function section(Request $request): mixed
    {
        return MenuSection::make('Marketplace')
            ->path('/marketplace')
            ->icon('shopping-cart');
    }
}
