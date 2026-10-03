<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Traits\LoadsAndCachesTrait;
use App\Http\Controllers\Web\Traits\SharesPropsTrait;
use App\Http\Resources\Dish\DishMenuCollection;
use App\Http\Resources\Restaurant\RestaurantResource;
use App\Repositories\RestaurantReviewRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class PreviewController extends Controller
{
    use SharesPropsTrait;
    use LoadsAndCachesTrait;

    /**
     * PreviewController constructor.
     *
     * @param RestaurantReviewRepository $reviews
     */
    public function __construct(protected RestaurantReviewRepository $reviews)
    {
    }

    /**
     * Returns the restaurant page, one of its menus or its reviews.
     *
     * @param Request $request
     *
     * @return View|RedirectResponse
     */
    public function show(Request $request): View|RedirectResponse
    {
        // Content in the page's language (it falls back to another one, if there's no translation)
        $locale = $this->getSharedProp($request, 'locale');

        if (in_array($locale, config('app.supported_locales'), true)) {
            App::setLocale($locale);
        }

        // Guests always see the same page: what's archived, hidden or deleted is never shown,
        // whoever asks and however (it's cached for everyone)
        $request->query->remove('archived');
        $request->query->remove('deleted');
        $request->query->remove('filter');

        $restaurant = $this->loadAndCacheRestaurant($request->route('restaurant_id'));

        if (!$restaurant) {
            abort(404);
        }

        $menus = $this->loadAndCacheMenus($restaurant);

        if ($request->routeIs('web.menu.preview')) {
            // the query is kept (e.g. `?editor=1` of the editor's preview)
            $parameters = [
                ...$request->query(),
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
            // not cached: an approved review shows right away
            'reviews' => $this->reviews->summary($restaurant),
        ]);
    }
}
