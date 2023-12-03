<?php

namespace App\Nova\Dashboards;

use Andriichello\Metrics\MetricsCard;
use Laravel\Nova\Dashboards\Main as Dashboard;

class Metrics extends Dashboard
{
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
