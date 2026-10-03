<?php

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Admin\AdminPageController;
use App\Http\Requests\Editor\ShowRestaurantRequest;
use App\Http\Resources\Editor\EditorRestaurantResource;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Class EditorPageController.
 *
 * The admin editor's page: a Vue app, which changes a restaurant through the editor's API
 * and shows its public page in a preview.
 */
class EditorPageController extends AdminPageController
{
    /**
     * Open the editor of the current restaurant (the user's own one, or the one picked last).
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function index(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $restaurant = $this->currentRestaurant($request, $user);

        abort_unless((bool) $restaurant, 403);

        return redirect()->route('admin.editor', ['id' => $restaurant->id]);
    }

    /**
     * The editor of the restaurant, with everything of it in all languages.
     * It becomes the current restaurant.
     *
     * @param ShowRestaurantRequest $request
     *
     * @return View
     */
    public function show(ShowRestaurantRequest $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $restaurant = $request->restaurant();

        $request->session()->put(static::RESTAURANT_KEY, $restaurant->id);

        return $this->page($request, 'editor', "$restaurant->name · Page editor", [
            ...$this->signedIn($user, $restaurant),
            'restaurant' => new EditorRestaurantResource($this->restaurants->load($restaurant)),
        ]);
    }
}
