<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\Editor\EditorDashboardResource;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Class DashboardController.
 *
 * The admin's home: the restaurant's page, whether it's open now, upcoming special days and
 * scheduled changes.
 */
class DashboardController extends AdminPageController
{
    /**
     * The dashboard of the current restaurant. Staff, who can't edit a restaurant, go to the admin panel.
     *
     * @param Request $request
     *
     * @return View|RedirectResponse
     */
    public function show(Request $request): View|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $restaurant = $this->currentRestaurant($request, $user);

        if (!$restaurant) {
            abort_unless($user->isStaff(), 403);

            return redirect()->route('filament.admin.pages.dashboard');
        }

        $dashboard = new EditorDashboardResource($this->restaurants->dashboard($restaurant));

        return $this->page($request, 'dashboard', $restaurant->name, [
            ...$this->signedIn($user, $restaurant),
            'restaurant' => $dashboard,
        ]);
    }
}
