<?php

namespace App\Http\Controllers\Inertia;

use App\Helpers\RestaurantHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Menu\MenuCollection;
use App\Http\Resources\Restaurant\RestaurantResource;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Class MenuController.
 */
class MenuController extends Controller
{
    /**
     * Returns menu page for UI with Inertia.js.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function show(Request $request): Response
    {
        $restaurant = RestaurantHelper::find($request->route('restaurant_id'));

        if (!$restaurant) {
            abort(404);
        }

        $menuId = (int) $request->route('menu_id');
        /** @var Menu|null $menu */
        $menu = $restaurant->menus
            ->sortByDesc('popularity')
            ->where('id', $menuId)
            ->first();

        if (!$menu) {
            abort(404);
        }

        foreach ($restaurant->menus as $menu) {
            $menu->load('products');
        }

        return Inertia::render('Menu', [
            'menuId' => $menuId,
            'restaurant' => new RestaurantResource($restaurant),
            'menus' => new MenuCollection(
                $restaurant->menus
                    ->sortByDesc('popularity')
            ),
        ]);
    }
}
