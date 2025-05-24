<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Inertia\Traits\LoadsAndCachesTrait;
use App\Http\Resources\Menu\MenuCollection;
use App\Http\Resources\Restaurant\RestaurantResource;
use App\Http\Responses\ApiResponse;
use App\Models\Menu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Class MenuController.
 */
class MenuController extends Controller
{
    use LoadsAndCachesTrait;

    /**
     * Returns menu page for UI with Inertia.js.
     *
     * @param Request $request
     *
     * @return Response|RedirectResponse
     */
    public function show(Request $request): Response|RedirectResponse
    {
        $restaurant = $this->loadAndCacheRestaurant($request->route('restaurant_id'));

        if (!$restaurant) {
            abort(404);
        }

        $menus = $this->loadAndCacheMenus($restaurant);
        $menuId = (int) $request->route('menu_id');

        if (empty($menuId)) {
            /** @var Menu|null $menu */
            $menu = $menus->first();

            if (!$menu) {
                abort(404);
            }

            return redirect()
                ->route(
                    'inertia.menu.show',
                    [
                        'locale' => $request->route('locale'),
                        'restaurant_id' => $request->route('restaurant_id'),
                        'menu_id' => $menu->id,
                    ]
                );
        }

        /** @var Menu|null $menu */
        $menu = $menus->where('id', $menuId)->first();

        if (!$menu) {
            abort(404);
        }

        foreach ($menus as $m) {
            $m->setRelation('products', $this->loadAndCacheProductsFor($m));
        }

        return Inertia::render('Menu', [
            'menu_id' => $menu->id,
            'restaurant' => new RestaurantResource($restaurant),
            'menus' => new MenuCollection($menus),
        ]);
    }

    /**
     * Returns menus with products loaded.
     *
     * @param Request $request
     *
     * @return ApiResponse
     */
    public function menusWithProducts(Request $request): ApiResponse
    {
        $restaurant = $this->loadAndCacheRestaurant($request->route('restaurant_id'));

        if (!$restaurant) {
            return ApiResponse::make([], 404, 'Not Found');
        }

        $menus = $this->loadAndCacheMenus($restaurant);

        foreach ($menus as $m) {
            $m->setRelation('products', $this->loadAndCacheProductsFor($m));
        }

        return ApiResponse::make(['data' => new MenuCollection($menus)]);
    }
}
