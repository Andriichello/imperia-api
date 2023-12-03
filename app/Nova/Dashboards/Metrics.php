<?php

namespace App\Nova\Dashboards;

use Andriichello\Metrics\MetricsCard;
use Laravel\Nova\Dashboards\Main as Dashboard;

class Metrics extends Dashboard
{
    /**
     * Get the displayable name of the dashboard.
     *
     * @return string
     */
    public function name(): string
    {
        return __('nova.dashboard.metrics');
    }

    /**
     * Get the cards for the dashboard.
     *
     * @return array
     */
    public function cards(): array
    {
        return [
            new MetricsCard(),
        ];
    }
}
