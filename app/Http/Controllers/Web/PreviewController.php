<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Traits\LoadsAndCachesTrait;
use App\Http\Resources\Dish\DishMenuCollection;
use App\Http\Resources\Restaurant\RestaurantResource;
use App\Models\Menu;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PreviewController extends Controller
{
    use LoadsAndCachesTrait;

    /**
     * Returns menu page for UI with Inertia.js.
     *
     * @param Request $request
     *
     * @return View|RedirectResponse
     */
    public function show(Request $request): View|RedirectResponse
    {
        $restaurant = $this->loadAndCacheRestaurant($request->route('restaurant_id'));

        if (!$restaurant) {
            abort(404);
        }

        $menus = $this->loadAndCacheMenus($restaurant);

        if (str_ends_with($request->path(), '/menu')) {
            $menuId = (int)$request->route('menu_id');

            if (empty($menuId)) {
                /** @var Menu|null $menu */
                $menu = $menus->first();

                if (!$menu) {
                    abort(404);
                }

                return redirect()
                    ->route(
                        'web.menu.preview',
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

        return view('web.restaurant.preview', [
            'restaurant' => new RestaurantResource($restaurant),
            'menus' => new DishMenuCollection($menus),
        ]);
    }
}
