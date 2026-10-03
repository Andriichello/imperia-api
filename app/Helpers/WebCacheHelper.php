<?php

namespace App\Helpers;

use App\Models\Restaurant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Class WebCacheHelper.
 *
 * Cache of the public website's restaurant and menu pages (see `LoadsAndCachesTrait`).
 */
class WebCacheHelper
{
    /**
     * Cache key of a restaurant, by the id or slug used in the URL.
     *
     * @param int|string $idOrSlug
     *
     * @return string
     */
    public static function restaurantKey(int|string $idOrSlug): string
    {
        // the same key for "01" and "1", or "First" and "first", which find the same restaurant
        return 'web_restaurant_' . (is_numeric($idOrSlug) ? (int) $idOrSlug : Str::lower($idOrSlug));
    }

    /**
     * Cache key of a restaurant's menus.
     *
     * @param int $restaurantId
     *
     * @return string
     */
    public static function menusKey(int $restaurantId): string
    {
        return 'web_menus_for_' . $restaurantId;
    }

    /**
     * Forget the cached pages of the restaurants with given ids.
     *
     * @param int|null ...$ids
     *
     * @return void
     */
    public static function forgetRestaurants(?int ...$ids): void
    {
        $ids = array_unique(array_filter($ids));

        if (empty($ids)) {
            return;
        }

        /** @var Restaurant[] $restaurants */
        $restaurants = Restaurant::query()
            ->withoutGlobalScopes()
            ->whereIn('id', $ids)
            ->get();

        foreach ($restaurants as $restaurant) {
            static::forgetRestaurant($restaurant);
        }
    }

    /**
     * Forget the cached pages of the restaurant (by its id and slugs) and its menus. Its content
     * version goes up: its dishes get a new snapshot (see `MenuSnapshotRepository`).
     *
     * @param Restaurant $restaurant
     *
     * @return void
     */
    public static function forgetRestaurant(Restaurant $restaurant): void
    {
        // a query: no events of the restaurant again, and its update time stays
        Restaurant::query()
            ->withoutGlobalScopes()
            ->whereKey($restaurant->id)
            ->toBase()
            ->increment('content_version');

        $keys = [
            static::restaurantKey($restaurant->id),
            static::menusKey($restaurant->id),
        ];

        // the slug before saving too, in case it has changed
        foreach (array_filter([$restaurant->slug, $restaurant->getOriginal('slug')]) as $slug) {
            $keys[] = static::restaurantKey($slug);
        }

        foreach (array_unique($keys) as $key) {
            Cache::forget($key);
        }
    }
}
