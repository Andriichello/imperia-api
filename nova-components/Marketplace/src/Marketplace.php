<?php

namespace Andriichello\Marketplace;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
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
     * Build the view that renders the navigation links for the tool.
     *
     * @return \Illuminate\Contracts\View\View|Factory|string|View|Application
     */
    public function renderNavigation(): \Illuminate\Contracts\View\View|Factory|string|View|Application
    {
        return view('marketplace::navigation');
    }
}
