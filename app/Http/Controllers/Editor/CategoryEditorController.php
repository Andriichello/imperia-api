<?php

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Editor\CategoryActionRequest;
use App\Http\Requests\Editor\EditorRequest;
use App\Http\Requests\Editor\MoveCategoryRequest;
use App\Http\Requests\Editor\StoreCategoryRequest;
use App\Http\Requests\Editor\UpdateCategoryRequest;
use App\Http\Resources\Editor\EditorCategoryResource;
use App\Http\Responses\ApiResponse;
use App\Models\DishCategory;
use App\Models\DishMenu;
use App\Models\Scopes\ArchivedScope;
use App\Repositories\Editor\CategoryEditorRepository;
use Illuminate\Database\Eloquent\Relations\Relation;
use OpenApi\Annotations as OA;

/**
 * Class CategoryEditorController.
 *
 * The admin editor's categories: create, update (with the order of their dishes), move,
 * hide, archive, restore, duplicate and delete (only from the archive).
 */
class CategoryEditorController extends Controller
{
    /**
     * CategoryEditorController constructor.
     *
     * @param CategoryEditorRepository $repository
     */
    public function __construct(protected CategoryEditorRepository $repository)
    {
    }

    /**
     * Create a category at the end of the menu.
     *
     * @param StoreCategoryRequest $request
     *
     * @return ApiResponse
     */
    public function store(StoreCategoryRequest $request): ApiResponse
    {
        /** @var DishMenu $menu */
        $menu = $request->target();

        return $this->respond($this->repository->create($menu, $request->validated()), 201, 'Created');
    }

    /**
     * Update texts and visibility of the category, and the order of its dishes.
     *
     * @param UpdateCategoryRequest $request
     *
     * @return ApiResponse
     */
    public function update(UpdateCategoryRequest $request): ApiResponse
    {
        /** @var DishCategory $category */
        $category = $request->target();

        return $this->respond($this->repository->update($category, $request->validated()));
    }

    /**
     * Move the category with its dishes to the end of another menu.
     *
     * @param MoveCategoryRequest $request
     *
     * @return ApiResponse
     */
    public function move(MoveCategoryRequest $request): ApiResponse
    {
        /** @var DishCategory $category */
        $category = $request->target();
        /** @var DishMenu $menu */
        $menu = EditorRequest::findForEditor(DishMenu::class, (int) $request->validated('menu_id'));

        return $this->respond($this->repository->move($category, $menu));
    }

    /**
     * Move the category to the archive.
     *
     * @param CategoryActionRequest $request
     *
     * @return ApiResponse
     */
    public function archive(CategoryActionRequest $request): ApiResponse
    {
        /** @var DishCategory $category */
        $category = $this->repository->archive($request->target());

        return $this->respond($category);
    }

    /**
     * Restore the category from the archive.
     *
     * @param CategoryActionRequest $request
     *
     * @return ApiResponse
     */
    public function unarchive(CategoryActionRequest $request): ApiResponse
    {
        /** @var DishCategory $category */
        $category = $this->repository->unarchive($request->target());

        return $this->respond($category);
    }

    /**
     * Copy the category with its dishes (the copy is hidden).
     *
     * @param CategoryActionRequest $request
     *
     * @return ApiResponse
     */
    public function duplicate(CategoryActionRequest $request): ApiResponse
    {
        /** @var DishCategory $category */
        $category = $request->target();

        return $this->respond($this->repository->duplicate($category), 201, 'Created');
    }

    /**
     * Delete the category with its dishes, which is possible only from the archive.
     *
     * @param CategoryActionRequest $request
     *
     * @return ApiResponse
     */
    public function destroy(CategoryActionRequest $request): ApiResponse
    {
        $this->repository->deleteArchived($request->target());

        return ApiResponse::make([], 200, 'Deleted');
    }

    /**
     * The category with its dishes (hidden and archived ones included).
     *
     * @param DishCategory $category
     * @param int $status
     * @param string $message
     *
     * @return ApiResponse
     */
    protected function respond(DishCategory $category, int $status = 200, string $message = 'Success'): ApiResponse
    {
        $category->load([
            'dishes' => fn (Relation $query) => $query->withoutGlobalScope(ArchivedScope::class),
            'dishes.variants',
            'dishes.media',
        ]);

        return ApiResponse::make(['data' => new EditorCategoryResource($category)], $status, $message);
    }

    /**
     * @OA\Post(
     *   path="/api/editor/menus/{id}/categories",
     *   summary="Create a category at the end of the menu.",
     *   operationId="storeEditorCategory",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the menu."),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/EditorStoreCategoryRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Created.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorCategoryResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     *   @OA\Response(response=422, description="Invalid values.",
     *     @OA\JsonContent(ref="#/components/schemas/ValidationErrorsResponse")),
     * ),
     * @OA\Patch(
     *   path="/api/editor/categories/{id}",
     *   summary="Update texts and visibility of the category, and the order of its dishes.",
     *   operationId="updateEditorCategory",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the category."),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/EditorUpdateCategoryRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorCategoryResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     *   @OA\Response(response=422, description="Invalid values.",
     *     @OA\JsonContent(ref="#/components/schemas/ValidationErrorsResponse")),
     * ),
     * @OA\Post(
     *   path="/api/editor/categories/{id}/move",
     *   summary="Move the category with its dishes to another menu.",
     *   operationId="moveEditorCategory",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the category."),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/EditorMoveCategoryRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorCategoryResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     *   @OA\Response(response=422, description="Invalid values.",
     *     @OA\JsonContent(ref="#/components/schemas/ValidationErrorsResponse")),
     * ),
     * @OA\Post(
     *   path="/api/editor/categories/{id}/archive",
     *   summary="Move the category to the archive.",
     *   operationId="archiveEditorCategory",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the category."),
     *   @OA\Response(
     *     response=200,
     *     description="Success.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorCategoryResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     * ),
     * @OA\Post(
     *   path="/api/editor/categories/{id}/unarchive",
     *   summary="Restore the category from the archive.",
     *   operationId="unarchiveEditorCategory",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the category."),
     *   @OA\Response(
     *     response=200,
     *     description="Success.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorCategoryResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     * ),
     * @OA\Post(
     *   path="/api/editor/categories/{id}/duplicate",
     *   summary="Copy the category with its dishes (the copy is hidden).",
     *   operationId="duplicateEditorCategory",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the category."),
     *   @OA\Response(
     *     response=201,
     *     description="Created.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorCategoryResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     * ),
     * @OA\Delete(
     *   path="/api/editor/categories/{id}",
     *   summary="Delete the category with its dishes (only from the archive).",
     *   operationId="destroyEditorCategory",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the category."),
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
     *   schema="EditorCategoryResponse",
     *   description="Category with its dishes.",
     *   required={"data", "message"},
     *   @OA\Property(property="data", ref="#/components/schemas/EditorCategory"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     */
}
