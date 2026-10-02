<?php

namespace App\Subscribers;

use App\Helpers\WebCacheHelper;
use App\Models\BaseModel;
use App\Models\DishCategory;
use App\Models\DishMenu;
use App\Models\Restaurant;
use App\Models\Schedule;

/**
 * Class WebCacheSubscriber.
 *
 * Clears the cached restaurant and menu pages of the website when something
 * shown there changes, so the change doesn't wait for the cache to expire.
 */
class WebCacheSubscriber extends BaseSubscriber
{
    /**
     * Models to the methods, which clear the cache for them.
     *
     * @var array<string, string>
     */
    protected array $models = [
        Restaurant::class => 'restaurantChanged',
        Schedule::class => 'scheduleChanged',
        DishMenu::class => 'menuChanged',
        DishCategory::class => 'categoryChanged',
    ];

    protected function map(): void
    {
        $map = [];

        foreach ($this->models as $model => $method) {
            /** @var BaseModel $model */
            $map[$model::eloquentEvent('saved')] = $method;
            $map[$model::eloquentEvent('deleted')] = $method;
            $map[$model::eloquentEvent('restored')] = $method;
        }

        $this->map = $map;
    }

    /**
     * @param Restaurant $restaurant
     *
     * @return void
     */
    public function restaurantChanged(Restaurant $restaurant): void
    {
        WebCacheHelper::forgetRestaurant($restaurant);
    }

    /**
     * @param Schedule $schedule
     *
     * @return void
     */
    public function scheduleChanged(Schedule $schedule): void
    {
        WebCacheHelper::forgetRestaurants($schedule->restaurant_id, $schedule->getOriginal('restaurant_id'));
    }

    /**
     * @param DishMenu $menu
     *
     * @return void
     */
    public function menuChanged(DishMenu $menu): void
    {
        WebCacheHelper::forgetRestaurants($menu->restaurant_id, $menu->getOriginal('restaurant_id'));
    }

    /**
     * @param DishCategory $category
     *
     * @return void
     */
    public function categoryChanged(DishCategory $category): void
    {
        $restaurantIds = DishMenu::query()
            ->withoutGlobalScopes()
            ->whereIn('id', array_filter([$category->menu_id, $category->getOriginal('menu_id')]))
            ->pluck('restaurant_id')
            ->all();

        WebCacheHelper::forgetRestaurants(...$restaurantIds);
    }
}
