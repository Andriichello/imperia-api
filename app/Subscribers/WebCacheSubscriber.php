<?php

namespace App\Subscribers;

use App\Helpers\WebCacheHelper;
use App\Models\BaseModel;
use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\DishMenu;
use App\Models\DishVariant;
use App\Models\Restaurant;
use App\Models\RestaurantNote;
use App\Models\Schedule;
use App\Models\ScheduleException;

/**
 * Class WebCacheSubscriber.
 *
 * Clears the cached restaurant and menu pages of the website when something
 * shown there changes, so the change doesn't wait for the cache to expire: the
 * restaurant's content version goes up too, so its dishes get a new snapshot.
 * Changes of dishes and their sizes made outside the editor (the admin panel,
 * the API) count too.
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
        Dish::class => 'dishChanged',
        DishVariant::class => 'sizeChanged',
        RestaurantNote::class => 'restaurantItemChanged',
        ScheduleException::class => 'restaurantItemChanged',
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
     * Forget the cached website pages of the note's or special day's restaurant.
     *
     * @param RestaurantNote|ScheduleException $item
     *
     * @return void
     */
    public function restaurantItemChanged(RestaurantNote|ScheduleException $item): void
    {
        WebCacheHelper::forgetRestaurants($item->restaurant_id, $item->getOriginal('restaurant_id'));
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
        $menuIds = [$category->menu_id, $category->getOriginal('menu_id')];

        WebCacheHelper::forgetRestaurants(...$this->restaurantsOfMenus(...$menuIds));
    }

    /**
     * @param Dish $dish
     *
     * @return void
     */
    public function dishChanged(Dish $dish): void
    {
        WebCacheHelper::forgetRestaurants(...$this->restaurantsOfMenus($dish->menu_id, $dish->getOriginal('menu_id')));
    }

    /**
     * @param DishVariant $size
     *
     * @return void
     */
    public function sizeChanged(DishVariant $size): void
    {
        $menuIds = Dish::query()
            ->withoutGlobalScopes()
            ->whereIn('id', array_filter([$size->dish_id, $size->getOriginal('dish_id')]))
            ->pluck('menu_id')
            ->all();

        WebCacheHelper::forgetRestaurants(...$this->restaurantsOfMenus(...$menuIds));
    }

    /**
     * Ids of the restaurants of the menus (hidden and archived ones too).
     *
     * @param int|null ...$menuIds
     *
     * @return int[]
     */
    protected function restaurantsOfMenus(?int ...$menuIds): array
    {
        return DishMenu::query()
            ->withoutGlobalScopes()
            ->whereIn('id', array_filter($menuIds))
            ->pluck('restaurant_id')
            ->all();
    }
}
