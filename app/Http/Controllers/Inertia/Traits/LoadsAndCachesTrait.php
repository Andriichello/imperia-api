<?php

namespace App\Http\Controllers\Inertia\Traits;

use App\Helpers\RestaurantHelper;
use App\Models\Menu;
use App\Models\Product;
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
    protected function loadAndCacheRestaurant(int|string|null $idOrSlug, int $ttl = 5): ?Restaurant
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
     * @return Collection<int, Menu>
     */
    protected function loadAndCacheMenus(Restaurant $restaurant, int $ttl = 5): Collection
    {
        $key = 'inertia_menus_for_' . $restaurant->id;
        $callback = fn() => $restaurant->menus
            ->sortByDesc('popularity')
            ->each(fn($menu) => $menu->load(['categories', 'media', 'media.variants']))
            ->values();

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Get products for the given restaurant (caches the result).
     *
     * @param Restaurant|int $restaurant
     * @param int $ttl Time to live (in seconds)
     *
     * @return Collection<int, Product>
     */
    protected function loadAndCacheProducts(Restaurant|int $restaurant, int $ttl = 5): Collection
    {
        if (is_numeric($restaurant)) {
            $restaurant = new Restaurant(['id' => $restaurant]);
        }

        $key = 'inertia_restaurant_' . $restaurant->id . '_products';
        $callback = function () use ($restaurant) {


            /** @var array<int, array{menu_id: int, product_ids: int[]}> $map */
            $map = $restaurant->products()->getQuery()
                ->join('menu_product', 'products.id', '=', 'menu_product.product_id')
                ->groupBy('menu_product.menu_id')
                ->selectRaw('menu_product.menu_id as menu_id,' .
                    ' group_concat(menu_product.product_id separator \';\') as product_ids')
                ->get()
                ->map(fn(Product $p) => [
                    'menu_id' => data_get($p, 'menu_id'),
                    'product_ids' => array_map(
                        fn($id) => (int) $id,
                        explode(';', data_get($p, 'product_ids', ''))
                    ),
                ]);

            return $restaurant->products
                ->sortByDesc('popularity')
                ->each(fn(Product $product) => $product->load(['media', 'media.variants']))
                ->each(function (Product $product) use ($map) {
                    $menuIds = [];

                    foreach ($map as $values) {
                        if (in_array($product->id, $values['product_ids'])) {
                            $menuIds[] = $values['menu_id'];
                        }
                    }

                    $product->menuIds = $menuIds;
                })
                ->values();
        };

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Get products for the given menu (caches the result).
     *
     * @param Menu|int $menu
     * @param int $ttl Time to live (in seconds)
     *
     * @return Collection<int, Product>
     */
    protected function loadAndCacheProductsFor(Menu|int $menu, int $ttl = 5): Collection
    {
        if (is_numeric($menu)) {
            $menu = new Menu(['id' => $menu]);
        }

        $key = 'inertia_menu_' . $menu->id . '_products';
        $callback = fn() => $menu->products
            ->sortByDesc('popularity')
            ->each(fn($menu) => $menu->load(['media', 'media.variants']))
            ->values();

        return Cache::remember($key, $ttl, $callback);
    }
}
