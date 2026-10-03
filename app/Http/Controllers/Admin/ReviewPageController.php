<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\Editor\EditorDashboardResource;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Class ReviewPageController.
 *
 * Reviews guests left of the current restaurant, for the restaurant to approve or reject them
 * (through the editor's API).
 */
class ReviewPageController extends AdminPageController
{
    /**
     * The reviews of the current restaurant. Without a restaurant to edit, the dashboard tells
     * about it.
     *
     * @param Request $request
     *
     * @return View|RedirectResponse
     */
    public function index(Request $request): View|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $restaurant = $this->currentRestaurant($request, $user);

        if (!$restaurant) {
            return redirect()->route('admin.dashboard');
        }

        return $this->page($request, 'reviews', "Reviews · $restaurant->name", [
            ...$this->signedIn($user, $restaurant),
            'restaurant' => new EditorDashboardResource($this->restaurants->dashboard($restaurant)),
        ]);
    }
}
