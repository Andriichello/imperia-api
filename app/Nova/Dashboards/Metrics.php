<?php

namespace App\Nova\Dashboards;

use Andriichello\Metrics\MetricsCard;
use App\Models\User;
use Illuminate\Http\Request;
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
        /** @var User|null $user */
        $user = request()->user();

        $restaurantId = $user?->restaurant_id ?? 0;
        $isSuper = $user && $user->isAdmin() && !$user->isPreviewOnly();

        return [
            (new MetricsCard())
                ->restaurant($restaurantId)
                ->withMeta(compact('isSuper'))
                ->canSee(function (Request $request) use ($restaurantId) {
                    /** @var User|null $user */
                    $user = $request->user();

                    if (!$user || !$user->isAdmin()) {
                        return false;
                    }

                    return !$user->isPreviewOnly()
                        || $user->restaurant_id === $restaurantId;
                }),
        ];
    }
}
