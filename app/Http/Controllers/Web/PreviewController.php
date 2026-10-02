<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Traits\LoadsAndCachesTrait;
use App\Http\Controllers\Web\Traits\SharesPropsTrait;
use App\Http\Resources\Dish\DishMenuCollection;
use App\Http\Resources\Restaurant\RestaurantResource;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PreviewController extends Controller
{
    use SharesPropsTrait;
    use LoadsAndCachesTrait;

    /**
     * Returns the restaurant page or one of its menus.
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

        if ($request->routeIs('web.menu.preview')) {
            $parameters = [
                'locale' => $request->route('locale'),
                'restaurant_id' => $request->route('restaurant_id'),
            ];

            $menuId = (int)$request->route('menu_id');

            // No menu given: open the first one
            if (!$menuId && $menus->isNotEmpty()) {
                return redirect()->route('web.menu.preview', [...$parameters, 'menu_id' => $menus->first()->id]);
            }

            // A menu that doesn't exist or is hidden (e.g. an old link): open the restaurant page
            if (!$menus->contains('id', $menuId)) {
                return redirect()->route('web.restaurant.preview', $parameters);
            }
        }

        return view('web.app', [
            ...$this->getSharedProps($request),
            'restaurant' => new RestaurantResource($restaurant),
            'menus' => new DishMenuCollection($menus),
        ]);
    }
}
