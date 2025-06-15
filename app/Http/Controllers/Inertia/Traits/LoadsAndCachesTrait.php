<?php

namespace App\Http\Controllers\Inertia\Traits;

use App\Helpers\RestaurantHelper;
use App\Models\Dish;
use App\Models\DishMenu;
use App\Models\Menu;
use App\Models\Restaurant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Trait LoadsAndCachesTrait.
 */
trait LoadsAndCachesTrait
{
    /**
     * Get the target restaurant (caches the result).
     *
     * @param int|string|null $idOrSlug
     * @param int $ttl Time to live (in seconds)
     *
     * @return Restaurant|null
     */
    protected function loadAndCacheRestaurant(int|string|null $idOrSlug, int $ttl = 120): ?Restaurant
    {
        if (is_null($idOrSlug)) {
            return null;
        }

        $key = 'inertia_restaurant_' . $idOrSlug;
        $callback = fn() => RestaurantHelper::find($idOrSlug)
            ?->load(['media', 'schedules']);

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Get menus for the given restaurant (also caches the result).
     *
     * @param Restaurant $restaurant
     * @param int $ttl Time to live (in seconds)
     *
     * @return Collection<int, DishMenu>
     */
    protected function loadAndCacheMenus(Restaurant $restaurant, int $ttl = 120): Collection
    {
        $key = 'inertia_menus_for_' . $restaurant->id;
        $callback = fn() => $restaurant->dishMenus
            ->sortByDesc('popularity')
            ->each(fn($menu) => $menu->load(['categories', 'media', 'media.variants']))
            ->values();

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Get dishes for the given restaurant (caches the result).
     *
     * @param Restaurant|int $restaurant
     * @param int $ttl Time to live (in seconds)
     *
     * @return Collection<int, Dish>
     */
    protected function loadAndCacheDishes(Restaurant|int $restaurant, int $ttl = 120): Collection
    {
        if (is_numeric($restaurant)) {
            $restaurant = new Restaurant(['id' => $restaurant]);
        }

        $key = 'inertia_restaurant_' . $restaurant->id . '_products';
        $callback = function () use ($restaurant) {
            return $restaurant->dishes
                ->sortByDesc('popularity')
                ->each(fn(Dish $dish) => $dish->load(['media', 'media.variants']))
                ->values();
        };

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Get dishes for the given menu (caches the result).
     *
     * @param DishMenu|int $menu
     * @param int $ttl Time to live (in seconds)
     *
     * @return Collection<int, Dish>
     */
    protected function loadAndCacheDishesFor(DishMenu|int $menu, int $ttl = 120): Collection
    {
        if (is_numeric($menu)) {
            $menu = new Menu(['id' => $menu]);
        }

        $key = 'inertia_menu_' . $menu->id . '_products';
        $callback = fn() => $menu->dishes
            ->sortByDesc('popularity')
            ->each(fn($menu) => $menu->load(['media', 'media.variants']))
            ->values();

        return Cache::remember($key, $ttl, $callback);
    }
}
