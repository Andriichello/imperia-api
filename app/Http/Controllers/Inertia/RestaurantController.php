<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Controller;
use App\Http\Resources\Menu\MenuCollection;
use App\Http\Resources\Restaurant\RestaurantResource;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Class RestaurantController.
 */
class RestaurantController extends Controller
{
    /**
     * Returns welcome page for UI with Inertia.js.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function show(Request $request): Response
    {
        /** @var Restaurant|null $restaurant */
        $restaurant = Restaurant::query()
            ->where('id', $request->route('id'))
            ->first();

        if (!$restaurant) {
            abort(404);
        }

        return Inertia::render('Restaurant', [
            'restaurant' => new RestaurantResource($restaurant),
            'menus' => new MenuCollection(
                /** @phpstan-ignore-next-line  */
                $restaurant->menus()
                    ->orderByDesc('popularity')
                    ->get()
            ),
        ]);
    }
}
