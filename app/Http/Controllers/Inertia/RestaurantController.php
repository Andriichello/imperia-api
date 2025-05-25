<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Inertia\Traits\LoadsAndCachesTrait;
use App\Http\Resources\Menu\MenuCollection;
use App\Http\Resources\Restaurant\RestaurantResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Class RestaurantController.
 */
class RestaurantController extends Controller
{
    use LoadsAndCachesTrait;

    /**
     * Returns welcome page for UI with Inertia.js.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function show(Request $request): Response
    {
        $restaurant = $this->loadAndCacheRestaurant($request->route('restaurant_id'));

        if (!$restaurant) {
            abort(404);
        }

        $menus = $this->loadAndCacheMenus($restaurant);

        foreach ($menus as $m) {
            $m->setRelation('products', $this->loadAndCacheProductsFor($m));
        }

        return Inertia::render('Restaurant', [
            'restaurant' => new RestaurantResource($restaurant),
            'menus' => new MenuCollection($menus),
        ]);
    }
}
