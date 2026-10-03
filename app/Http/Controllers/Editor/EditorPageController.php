<?php

namespace App\Http\Controllers\Editor;

use App\Helpers\ContentLocale;
use App\Http\Controllers\Controller;
use App\Http\Requests\Editor\ShowRestaurantRequest;
use App\Http\Resources\Editor\EditorRestaurantResource;
use App\Models\User;
use App\Repositories\Editor\RestaurantEditorRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Class EditorPageController.
 *
 * The admin editor's page: a Vue app, which changes a restaurant through the editor's API
 * and shows its public page in a preview. It's a page of the admin panel, signed in like it.
 */
class EditorPageController extends Controller
{
    /**
     * EditorPageController constructor.
     *
     * @param RestaurantEditorRepository $repository
     */
    public function __construct(protected RestaurantEditorRepository $repository)
    {
    }

    /**
     * Open the editor of the user's restaurant (the first one for admins of all restaurants).
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function index(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $restaurant = $this->repository->editableBy($user)->first();

        abort_unless((bool) $restaurant, 403);

        return redirect()->route('filament.admin.editor', ['id' => $restaurant['id']]);
    }

    /**
     * The editor of the restaurant, with everything of it in all languages.
     *
     * @param ShowRestaurantRequest $request
     *
     * @return View
     */
    public function show(ShowRestaurantRequest $request): View
    {
        /** @var User $user */
        $user = $request->user();

        return view('editor.app', [
            'props' => [
                // language of the editor itself (the browser's one), the restaurant's content has its own ones
                'locale' => $request->getPreferredLanguage(ContentLocale::supported()) ?? app()->getLocale(),
                'restaurant' => new EditorRestaurantResource($this->repository->load($request->restaurant())),
                'restaurants' => $this->repository->editableBy($user),
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'urls' => [
                    'admin' => route('filament.admin.pages.dashboard'),
                    'logout' => route('filament.admin.auth.logout'),
                ],
            ],
        ]);
    }
}
