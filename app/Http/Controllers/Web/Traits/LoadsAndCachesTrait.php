<?php

namespace App\Http\Controllers\Web\Traits;

use App\Helpers\RestaurantHelper;
use App\Helpers\WebCacheHelper;
use App\Models\DishMenu;
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

        $key = WebCacheHelper::restaurantKey($idOrSlug);
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
        $key = WebCacheHelper::menusKey($restaurant->id);
        $callback = fn() => $restaurant->dishMenus
            ->sortByDesc('popularity')
            ->each(fn($menu) => $menu->load(['categories', 'media', 'media.variants']))
            ->values();

        return Cache::remember($key, $ttl, $callback);
    }
}
