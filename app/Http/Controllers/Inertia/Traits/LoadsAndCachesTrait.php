<?php

namespace App\Http\Controllers\Inertia\Traits;

use App\Helpers\RestaurantHelper;
use App\Models\Menu;
use App\Models\Restaurant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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
    protected function loadAndCacheRestaurant(int|string|null $idOrSlug, int $ttl = 300): ?Restaurant
    {
        if (is_null($idOrSlug)) {
            return null;
        }

        $key = 'inertia_restaurant_' . $idOrSlug;
        $callback = fn() => RestaurantHelper::find($idOrSlug);

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Get menus for the given restaurant (also caches the result).
     *
     * @param Restaurant $restaurant
     * @param int $ttl Time to live (in seconds)
     *
     * @return Collection<int, Menu>
     */
    protected function loadAndCacheMenus(Restaurant $restaurant, int $ttl = 300): Collection
    {
        $key = 'inertia_menus_for_' . $restaurant->id;
        $callback = fn() => $restaurant->menus
            ->sortByDesc('popularity');

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Get products for the given menu (caches the result).
     *
     * @param Menu|int $menu
     * @param int $ttl Time to live (in seconds)
     *
     * @return Collection<int, Menu>
     */
    protected function loadAndCacheProductsFor(Menu|int $menu, int $ttl = 300): Collection
    {
        if (is_numeric($menu)) {
            $menu = new Menu(['id' => $menu]);
        }

        $key = 'inertia_menu_' . $menu->id . '_products';
        $callback = fn() => $menu->products
            ->sortByDesc('popularity');

        return Cache::remember($key, $ttl, $callback);
    }
}
