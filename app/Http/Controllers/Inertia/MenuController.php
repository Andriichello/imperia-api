<?php

namespace App\Http\Controllers\Inertia;

use App\Helpers\RestaurantHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Menu\MenuCollection;
use App\Http\Resources\Restaurant\RestaurantResource;
use App\Models\Menu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
     * @return Response|RedirectResponse
     */
    public function show(Request $request): Response|RedirectResponse
    {
        $restaurant = RestaurantHelper::find($request->route('restaurant_id'));

        if (!$restaurant) {
            abort(404);
        }

        $menuId = (int) $request->route('menu_id');

        /** @var Collection<int, Menu> $menus */
        $menus = $restaurant->menus
            ->sortByDesc('popularity');

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
            $m->load('products');
        }

        return Inertia::render('Menu', [
            'menuId' => $menu->id,
            'restaurant' => new RestaurantResource($restaurant),
            'menus' => new MenuCollection($menus),
        ]);
    }
}
