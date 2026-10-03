<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Editor\VersionActionRequest;
use App\Http\Resources\Editor\EditorRestaurantResource;
use App\Http\Resources\Editor\EditorVersionResource;
use App\Models\MenuVersion;
use App\Models\User;
use App\Repositories\Editor\RestaurantEditorRepository;
use App\Repositories\Editor\VersionEditorRepository;
use Illuminate\Contracts\View\View;

/**
 * Class VersionPageController.
 *
 * The page of a scheduled version: its items (the restaurant's menus, categories and dishes)
 * and their changes, which are edited right there through the editor's API.
 */
class VersionPageController extends AdminPageController
{
    /**
     * VersionPageController constructor.
     *
     * @param RestaurantEditorRepository $restaurants
     * @param VersionEditorRepository $versions
     */
    public function __construct(RestaurantEditorRepository $restaurants, protected VersionEditorRepository $versions)
    {
        parent::__construct($restaurants);
    }

    /**
     * The version with its changes, and everything of its restaurant. The restaurant becomes
     * the current one.
     *
     * @param VersionActionRequest $request
     *
     * @return View
     */
    public function show(VersionActionRequest $request): View
    {
        /** @var User $user */
        $user = $request->user();
        /** @var MenuVersion $version */
        $version = $request->target();
        $restaurant = $version->restaurant;

        $request->session()->put(static::RESTAURANT_KEY, $restaurant->id);

        return $this->page($request, 'version', ($version->name ?: 'Scheduled version') . " · $restaurant->name", [
            ...$this->signedIn($user, $restaurant),
            'restaurant' => new EditorRestaurantResource($this->restaurants->load($restaurant)),
            'version' => new EditorVersionResource($this->versions->load($version)),
        ]);
    }
}
