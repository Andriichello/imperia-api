<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Inertia\Traits\LoadsAndCachesTrait;
use App\Http\Resources\Menu\MenuCollection;
use App\Http\Resources\Product\ProductCollection;
use App\Http\Resources\Restaurant\RestaurantResource;
use App\Models\Menu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Class PreviewController.
 */
class PreviewController extends Controller
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

        if (str_ends_with($request->path(), '/menu')) {
            $menuId = (int) $request->route('menu_id');

            if (empty($menuId)) {
                /** @var Menu|null $menu */
                $menu = $menus->first();

                if (!$menu) {
                    abort(404);
                }

                return redirect()
                    ->route(
                        'inertia.menu.preview',
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
        }

        return Inertia::render('Preview', [
            'restaurant' => new RestaurantResource($restaurant),
            'menus' => new MenuCollection($menus),
            'products' => Inertia::defer(
                fn() => new ProductCollection($this->loadAndCacheProducts($restaurant))
            ),
//            'products' => Inertia::defer(
//                function() use ($restaurant) {
//                    sleep(3);
//                    return new ProductCollection($this->loadAndCacheProducts($restaurant));
//                }
//            ),
        ]);
    }
}
