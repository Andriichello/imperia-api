<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ContentLocale;
use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\User;
use App\Repositories\Editor\RestaurantEditorRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Class AdminPageController.
 *
 * Pages of the restaurant admin: Vue pages (see `resources/js/admin.ts`), which get their
 * props from the page. Signed-in pages are about the admin's current restaurant, which is
 * remembered in the session (`?restaurant=` switches it).
 */
abstract class AdminPageController extends Controller
{
    /**
     * Key of the current restaurant in the session.
     */
    public const RESTAURANT_KEY = 'admin.restaurant';

    /**
     * AdminPageController constructor.
     *
     * @param RestaurantEditorRepository $restaurants
     */
    public function __construct(protected RestaurantEditorRepository $restaurants)
    {
    }

    /**
     * The page with its props.
     *
     * @param Request $request
     * @param string $page name of the page (see `resources/js/admin.ts`)
     * @param string $title of the browser's tab
     * @param array $props
     *
     * @return View
     */
    protected function page(Request $request, string $page, string $title, array $props): View
    {
        return view('admin.app', [
            'page' => $page,
            'title' => $title,
            'props' => [
                // language of the admin itself (the browser's one), the restaurant's content has its own ones
                'locale' => $request->getPreferredLanguage(ContentLocale::supported()) ?? app()->getLocale(),
                ...$props,
            ],
        ]);
    }

    /**
     * Props of the signed-in pages: the user, the restaurants they can edit, where to go.
     *
     * @param User $user
     * @param Restaurant $restaurant the current one
     *
     * @return array
     */
    protected function signedIn(User $user, Restaurant $restaurant): array
    {
        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'restaurants' => $this->restaurants->editableBy($user),
            'urls' => [
                'dashboard' => route('admin.dashboard'),
                'editor' => route('admin.editor', ['id' => $restaurant->id]),
                'logout' => route('admin.logout'),
                // the admin panel, for the ones, who can open it
                'panel' => $user->isStaff() ? route('filament.admin.pages.dashboard') : null,
                'versions' => route('filament.admin.resources.menu-versions.index'),
            ],
        ];
    }

    /**
     * The user's current restaurant: the given one, the one picked in this session or their
     * first one. None, when they can't edit any.
     *
     * @param Request $request
     * @param User $user
     * @param int|null $id
     *
     * @return Restaurant|null
     */
    protected function currentRestaurant(Request $request, User $user, ?int $id = null): ?Restaurant
    {
        $editable = $this->restaurants->editableBy($user);
        $wanted = $id
            ?? ((int) $request->query('restaurant') ?: (int) $request->session()->get(static::RESTAURANT_KEY));
        $chosen = $editable->firstWhere('id', $wanted) ?? $editable->first();

        if (!$chosen) {
            return null;
        }

        $request->session()->put(static::RESTAURANT_KEY, $chosen['id']);

        /** @var Restaurant $restaurant */
        $restaurant = Restaurant::query()->findOrFail($chosen['id']);

        return $restaurant;
    }
}
