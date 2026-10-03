<?php

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Editor\MenuActionRequest;
use App\Http\Requests\Editor\OrderMenusRequest;
use App\Http\Requests\Editor\StoreMenuRequest;
use App\Http\Requests\Editor\UpdateMenuRequest;
use App\Http\Resources\Editor\EditorMenuResource;
use App\Http\Resources\Editor\EditorRestaurantResource;
use App\Http\Responses\ApiResponse;
use App\Models\DishMenu;
use App\Models\Restaurant;
use App\Models\Scopes\ArchivedScope;
use App\Repositories\Editor\MenuEditorRepository;
use App\Repositories\Editor\RestaurantEditorRepository;
use Illuminate\Database\Eloquent\Relations\Relation;
use OpenApi\Annotations as OA;

/**
 * Class MenuEditorController.
 *
 * The admin editor's menus: create, update, order, hide, archive, restore, duplicate
 * and delete (only from the archive).
 */
class MenuEditorController extends Controller
{
    /**
     * MenuEditorController constructor.
     *
     * @param MenuEditorRepository $repository
     */
    public function __construct(protected MenuEditorRepository $repository)
    {
    }

    /**
     * Create a menu at the end of the restaurant's menus.
     *
     * @param StoreMenuRequest $request
     *
     * @return ApiResponse
     */
    public function store(StoreMenuRequest $request): ApiResponse
    {
        $menu = $this->repository->create($request->restaurant(), $request->validated());

        return $this->respond($menu, 201, 'Created');
    }

    /**
     * Update texts and visibility of the menu.
     *
     * @param UpdateMenuRequest $request
     *
     * @return ApiResponse
     */
    public function update(UpdateMenuRequest $request): ApiResponse
    {
        /** @var DishMenu $menu */
        $menu = $request->target();

        return $this->respond($this->repository->update($menu, $request->validated()));
    }

    /**
     * Order menus of the restaurant and their categories (it responds with everything of it).
     *
     * @param OrderMenusRequest $request
     * @param RestaurantEditorRepository $restaurants
     *
     * @return ApiResponse
     */
    public function order(OrderMenusRequest $request, RestaurantEditorRepository $restaurants): ApiResponse
    {
        $this->repository->orderMenus($request->restaurant(), $request->validated('menus'));

        /** @var Restaurant $restaurant */
        $restaurant = $request->restaurant()->fresh();
        $data = new EditorRestaurantResource($restaurants->load($restaurant));

        return ApiResponse::make(compact('data'));
    }

    /**
     * Move the menu to the archive.
     *
     * @param MenuActionRequest $request
     *
     * @return ApiResponse
     */
    public function archive(MenuActionRequest $request): ApiResponse
    {
        /** @var DishMenu $menu */
        $menu = $this->repository->archive($request->target());

        return $this->respond($menu);
    }

    /**
     * Restore the menu from the archive.
     *
     * @param MenuActionRequest $request
     *
     * @return ApiResponse
     */
    public function unarchive(MenuActionRequest $request): ApiResponse
    {
        /** @var DishMenu $menu */
        $menu = $this->repository->unarchive($request->target());

        return $this->respond($menu);
    }

    /**
     * Copy the menu with everything inside it (the copy is hidden).
     *
     * @param MenuActionRequest $request
     *
     * @return ApiResponse
     */
    public function duplicate(MenuActionRequest $request): ApiResponse
    {
        /** @var DishMenu $menu */
        $menu = $request->target();

        return $this->respond($this->repository->duplicate($menu), 201, 'Created');
    }

    /**
     * Delete the menu with everything inside it, which is possible only from the archive.
     *
     * @param MenuActionRequest $request
     *
     * @return ApiResponse
     */
    public function destroy(MenuActionRequest $request): ApiResponse
    {
        $this->repository->deleteArchived($request->target());

        return ApiResponse::make([], 200, 'Deleted');
    }

    /**
     * The menu with its categories and dishes (hidden and archived ones included).
     *
     * @param DishMenu $menu
     * @param int $status
     * @param string $message
     *
     * @return ApiResponse
     */
    protected function respond(DishMenu $menu, int $status = 200, string $message = 'Success'): ApiResponse
    {
        $withArchived = fn (Relation $query) => $query->withoutGlobalScope(ArchivedScope::class);

        $menu->load([
            'categories' => $withArchived,
            'categories.dishes' => $withArchived,
            'categories.dishes.sizes',
            'categories.dishes.allMedia',
        ]);

        return ApiResponse::make(['data' => new EditorMenuResource($menu)], $status, $message);
    }

    /**
     * @OA\Post(
     *   path="/api/editor/restaurants/{id}/menus",
     *   summary="Create a menu at the end of the restaurant's menus.",
     *   operationId="storeEditorMenu",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the restaurant."),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/EditorStoreMenuRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Created.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorMenuResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     *   @OA\Response(response=422, description="Invalid values.",
     *     @OA\JsonContent(ref="#/components/schemas/ValidationErrorsResponse")),
     * ),
     * @OA\Put(
     *   path="/api/editor/restaurants/{id}/menus/order",
     *   summary="Order menus and their categories.",
     *   operationId="orderEditorMenus",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the restaurant."),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/EditorOrderMenusRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorRestaurantResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     *   @OA\Response(response=422, description="Invalid values.",
     *     @OA\JsonContent(ref="#/components/schemas/ValidationErrorsResponse")),
     * ),
     * @OA\Patch(
     *   path="/api/editor/menus/{id}",
     *   summary="Update texts and visibility of the menu.",
     *   operationId="updateEditorMenu",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the menu."),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/EditorUpdateMenuRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorMenuResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     *   @OA\Response(response=422, description="Invalid values.",
     *     @OA\JsonContent(ref="#/components/schemas/ValidationErrorsResponse")),
     * ),
     * @OA\Post(
     *   path="/api/editor/menus/{id}/archive",
     *   summary="Move the menu to the archive.",
     *   operationId="archiveEditorMenu",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the menu."),
     *   @OA\Response(
     *     response=200,
     *     description="Success.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorMenuResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     * ),
     * @OA\Post(
     *   path="/api/editor/menus/{id}/unarchive",
     *   summary="Restore the menu from the archive.",
     *   operationId="unarchiveEditorMenu",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the menu."),
     *   @OA\Response(
     *     response=200,
     *     description="Success.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorMenuResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     * ),
     * @OA\Post(
     *   path="/api/editor/menus/{id}/duplicate",
     *   summary="Copy the menu with everything inside it (the copy is hidden).",
     *   operationId="duplicateEditorMenu",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the menu."),
     *   @OA\Response(
     *     response=201,
     *     description="Created.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorMenuResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     * ),
     * @OA\Delete(
     *   path="/api/editor/menus/{id}",
     *   summary="Delete the menu with everything inside it (only from the archive).",
     *   operationId="destroyEditorMenu",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the menu."),
     *   @OA\Response(
     *     response=200,
     *     description="Success.",
     *     @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     *   @OA\Response(response=422, description="Invalid values.",
     *     @OA\JsonContent(ref="#/components/schemas/ValidationErrorsResponse")),
     * ),
     * @OA\Schema(
     *   schema="EditorMenuResponse",
     *   description="Menu with its categories and dishes.",
     *   required={"data", "message"},
     *   @OA\Property(property="data", ref="#/components/schemas/EditorMenu"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     */
}
