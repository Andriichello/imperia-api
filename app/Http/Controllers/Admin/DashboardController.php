<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\Editor\EditorDashboardResource;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Class DashboardController.
 *
 * The admin's home: the restaurant's page, its schedule and its changes, and planned changes
 * of its menu.
 */
class DashboardController extends AdminPageController
{
    /**
     * The dashboard of the current restaurant. Staff, who can't edit a restaurant, are told so
     * (they don't go to the admin panel).
     *
     * @param Request $request
     *
     * @return View
     */
    public function show(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $restaurant = $this->currentRestaurant($request, $user);

        if (!$restaurant) {
            abort_unless($user->isStaff(), 403);

            return $this->page($request, 'no-restaurant', __('Menu editor'), [
                'user' => ['name' => $user->name, 'email' => $user->email],
                'urls' => ['logout' => route('admin.logout')],
            ]);
        }

        $dashboard = new EditorDashboardResource($this->restaurants->dashboard($restaurant));

        return $this->page($request, 'dashboard', $restaurant->name, [
            ...$this->signedIn($user, $restaurant),
            'restaurant' => $dashboard,
        ]);
    }
}
