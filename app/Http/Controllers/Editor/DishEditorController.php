<?php

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Editor\DishActionRequest;
use App\Http\Requests\Editor\EditorRequest;
use App\Http\Requests\Editor\MoveDishRequest;
use App\Http\Requests\Editor\StoreDishRequest;
use App\Http\Requests\Editor\UpdateDishRequest;
use App\Http\Resources\Editor\EditorDishResource;
use App\Http\Responses\ApiResponse;
use App\Models\Dish;
use App\Models\DishCategory;
use App\Repositories\Editor\DishEditorRepository;
use OpenApi\Annotations as OA;

/**
 * Class DishEditorController.
 *
 * The admin editor's dishes: create, update, move, hide, archive, restore, duplicate
 * and delete (only from the archive).
 */
class DishEditorController extends Controller
{
    /**
     * DishEditorController constructor.
     *
     * @param DishEditorRepository $repository
     */
    public function __construct(protected DishEditorRepository $repository)
    {
    }

    /**
     * Create a dish at the end of the category.
     *
     * @param StoreDishRequest $request
     *
     * @return ApiResponse
     */
    public function store(StoreDishRequest $request): ApiResponse
    {
        /** @var DishCategory $category */
        $category = $request->target();

        return $this->respond($this->repository->create($category, $request->validated()), 201, 'Created');
    }

    /**
     * Update the dish.
     *
     * @param UpdateDishRequest $request
     *
     * @return ApiResponse
     */
    public function update(UpdateDishRequest $request): ApiResponse
    {
        /** @var Dish $dish */
        $dish = $request->target();

        return $this->respond($this->repository->update($dish, $request->validated()));
    }

    /**
     * Move the dish to the end of another category.
     *
     * @param MoveDishRequest $request
     *
     * @return ApiResponse
     */
    public function move(MoveDishRequest $request): ApiResponse
    {
        /** @var Dish $dish */
        $dish = $request->target();
        /** @var DishCategory $category */
        $category = EditorRequest::findForEditor(DishCategory::class, (int) $request->validated('category_id'));

        return $this->respond($this->repository->move($dish, $category));
    }

    /**
     * Move the dish to the archive.
     *
     * @param DishActionRequest $request
     *
     * @return ApiResponse
     */
    public function archive(DishActionRequest $request): ApiResponse
    {
        /** @var Dish $dish */
        $dish = $this->repository->archive($request->target());

        return $this->respond($dish);
    }

    /**
     * Restore the dish from the archive.
     *
     * @param DishActionRequest $request
     *
     * @return ApiResponse
     */
    public function unarchive(DishActionRequest $request): ApiResponse
    {
        /** @var Dish $dish */
        $dish = $this->repository->unarchive($request->target());

        return $this->respond($dish);
    }

    /**
     * Copy the dish with its sizes and photos (the copy is hidden).
     *
     * @param DishActionRequest $request
     *
     * @return ApiResponse
     */
    public function duplicate(DishActionRequest $request): ApiResponse
    {
        /** @var Dish $dish */
        $dish = $request->target();

        return $this->respond($this->repository->duplicate($dish), 201, 'Created');
    }

    /**
     * Delete the dish, which is possible only from the archive.
     *
     * @param DishActionRequest $request
     *
     * @return ApiResponse
     */
    public function destroy(DishActionRequest $request): ApiResponse
    {
        $this->repository->deleteArchived($request->target());

        return ApiResponse::make([], 200, 'Deleted');
    }

    /**
     * The dish with its sizes and photos.
     *
     * @param Dish $dish
     * @param int $status
     * @param string $message
     *
     * @return ApiResponse
     */
    protected function respond(Dish $dish, int $status = 200, string $message = 'Success'): ApiResponse
    {
        /** @var Dish $dish */
        $dish = $dish->fresh(['sizes', 'allMedia']);

        return ApiResponse::make(['data' => new EditorDishResource($dish)], $status, $message);
    }

    /**
     * @OA\Post(
     *   path="/api/editor/categories/{id}/dishes",
     *   summary="Create a dish at the end of the category.",
     *   operationId="storeEditorDish",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the category."),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/EditorStoreDishRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Created.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorDishResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     *   @OA\Response(response=422, description="Invalid values.",
     *     @OA\JsonContent(ref="#/components/schemas/ValidationErrorsResponse")),
     * ),
     * @OA\Patch(
     *   path="/api/editor/dishes/{id}",
     *   summary="Update the dish.",
     *   operationId="updateEditorDish",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the dish."),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/EditorUpdateDishRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorDishResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     *   @OA\Response(response=422, description="Invalid values.",
     *     @OA\JsonContent(ref="#/components/schemas/ValidationErrorsResponse")),
     * ),
     * @OA\Post(
     *   path="/api/editor/dishes/{id}/move",
     *   summary="Move the dish to another category.",
     *   operationId="moveEditorDish",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the dish."),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/EditorMoveDishRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorDishResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     *   @OA\Response(response=422, description="Invalid values.",
     *     @OA\JsonContent(ref="#/components/schemas/ValidationErrorsResponse")),
     * ),
     * @OA\Post(
     *   path="/api/editor/dishes/{id}/archive",
     *   summary="Move the dish to the archive.",
     *   operationId="archiveEditorDish",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the dish."),
     *   @OA\Response(
     *     response=200,
     *     description="Success.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorDishResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     * ),
     * @OA\Post(
     *   path="/api/editor/dishes/{id}/unarchive",
     *   summary="Restore the dish from the archive.",
     *   operationId="unarchiveEditorDish",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the dish."),
     *   @OA\Response(
     *     response=200,
     *     description="Success.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorDishResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     * ),
     * @OA\Post(
     *   path="/api/editor/dishes/{id}/duplicate",
     *   summary="Copy the dish with its sizes and photos (the copy is hidden).",
     *   operationId="duplicateEditorDish",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the dish."),
     *   @OA\Response(
     *     response=201,
     *     description="Created.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorDishResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     * ),
     * @OA\Delete(
     *   path="/api/editor/dishes/{id}",
     *   summary="Delete the dish (only from the archive).",
     *   operationId="destroyEditorDish",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the dish."),
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
     *   schema="EditorDishResponse",
     *   description="Dish with its sizes and photos.",
     *   required={"data", "message"},
     *   @OA\Property(property="data", ref="#/components/schemas/EditorDish"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     */
}
