<?php

namespace App\Http\Controllers\Model;

use App\Http\Controllers\CrudController;
use App\Http\Requests\Dish\DestroyDishRequest;
use App\Http\Requests\Dish\IndexDishRequest;
use App\Http\Requests\Dish\ShowDishRequest;
use App\Http\Requests\Dish\StoreDishRequest;
use App\Http\Requests\Dish\UpdateDishRequest;
use App\Http\Resources\Dish\DishCollection;
use App\Http\Resources\Dish\DishResource;
use App\Policies\DishPolicy;
use App\Repositories\DishRepository;

/**
 * Class DishController.
 */
class DishController extends CrudController
{
    /**
     * Controller's model resource class.
     *
     * @var string
     */
    protected string $resourceClass = DishResource::class;

    /**
     * Controller's model resource collection class.
     *
     * @var string
     */
    protected string $collectionClass = DishCollection::class;

    /**
     * DishController constructor.
     *
     * @param DishRepository $repository
     * @param DishPolicy $policy
     */
    public function __construct(DishRepository $repository, DishPolicy $policy)
    {
        parent::__construct($repository, $policy);

        $this->actions['index'] = IndexDishRequest::class;
        $this->actions['show'] = ShowDishRequest::class;
        $this->actions['store'] = StoreDishRequest::class;
        $this->actions['update'] = UpdateDishRequest::class;
        $this->actions['destroy'] = DestroyDishRequest::class;
    }

    /**
     * @OA\Get(
     *   path="/api/dishes",
     *   summary="Index dishes.",
     *   operationId="indexDishes",
     *   security={{"bearerAuth": {}}},
     *   tags={"dish.dishes"},
     *
     *   @OA\Parameter(name="include", in="query",
     *     @OA\Schema(ref ="#/components/schemas/DishIncludes")),
     *   @OA\Parameter(name="page[size]", in="query", @OA\Schema(ref ="#/components/schemas/PageSize")),
     *   @OA\Parameter(name="page[number]", in="query", @OA\Schema(ref ="#/components/schemas/PageNumber")),
     *   @OA\Parameter(name="sort", in="query", example="-popularity", @OA\Schema(type="string"),
     *        description="Available sorts: `popularity` (is default, but in descending order)"),
     *   @OA\Parameter(name="filter[ids]", required=false, in="query", example="1,2,3",
     *     @OA\Schema(type="string"), description="Coma-separated list of dish ids."),
     *   @OA\Parameter(name="filter[title]", required=false, in="query", example="Mojito",
     *     @OA\Schema(type="string"), description="Can be used for searches. Is partial."),
     *   @OA\Parameter(name="filter[menu_id]", required=false, in="query", example="1",
     *     @OA\Schema(type="string"), description="Menu ID to filter dishes by."),
     *   @OA\Parameter(name="filter[category_id]", required=false, in="query", example="2",
     *     @OA\Schema(type="string"), description="Category ID to filter dishes by."),
     *   @OA\Parameter(name="archived", in="query", @OA\Schema(ref ="#/components/schemas/ArchivedParameter")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Index dishes response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/IndexDishResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Get(
     *   path="/api/dishes/{id}",
     *   summary="Show dish by id.",
     *   operationId="showDish",
     *   security={{"bearerAuth": {}}},
     *   tags={"dish.dishes"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the dish."),
     *  @OA\Parameter(name="include", in="query",
     *     @OA\Schema(ref ="#/components/schemas/DishIncludes")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Show dish response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/ShowDishResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Post(
     *   path="/api/dishes",
     *   summary="Store dish.",
     *   operationId="storeDish",
     *   security={{"bearerAuth": {}}},
     *   tags={"dish.dishes"},
     *
     *  @OA\RequestBody(
     *     required=true,
     *     description="Store dish request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/StoreDishRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Create dish response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/StoreDishResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Patch(
     *   path="/api/dishes/{id}",
     *   summary="Update dish.",
     *   operationId="updateDish",
     *   security={{"bearerAuth": {}}},
     *   tags={"dish.dishes"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the dish."),
     *
     *  @OA\RequestBody(
     *     required=true,
     *     description="Update dish request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/UpdateDishRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Update dish response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/UpdateDishResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Delete(
     *   path="/api/dishes/{id}",
     *   summary="Delete dish.",
     *   operationId="destroyDish",
     *   security={{"bearerAuth": {}}},
     *   tags={"dish.dishes"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the dish."),
     *
     *  @OA\RequestBody(
     *     required=false,
     *     description="Delete dish request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/DestroyRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Delete dish response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/DestroyDishResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Post(
     *   path="/api/dishes/{id}/restore",
     *   summary="Restore dish.",
     *   operationId="restoreDish",
     *   security={{"bearerAuth": {}}},
     *   tags={"dish.dishes"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the dish."),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Restore dish response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/RestoreDishResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     *
     * @OA\Schema(
     *   schema="IndexDishResponse",
     *   description="Index dishes response object.",
     *   required = {"data", "meta", "message"},
     *   @OA\Property(property="data", type="array", @OA\Items(ref ="#/components/schemas/Dish")),
     *   @OA\Property(property="meta", ref ="#/components/schemas/PaginationMeta"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="ShowDishResponse",
     *   description="Show dish response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Dish"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="StoreDishResponse",
     *   description="Store dish response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Dish"),
     *   @OA\Property(property="message", type="string", example="Created"),
     * ),
     * @OA\Schema(
     *   schema="UpdateDishResponse",
     *   description="Update dish response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Dish"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="DestroyDishResponse",
     *   description="Delete dish response object.",
     *   required = {"message"},
     *   @OA\Property(property="message", type="string", example="Deleted"),
     * ),
     * @OA\Schema(
     *   schema="RestoreDishResponse",
     *   description="Restore dish response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Dish"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     *
     * @OA\Schema(
     *   schema="DishIncludes",
     *   description="Coma-separated list of inluded relations.
     *       Available relations: `menu`, `category`, `variants`, `media`",
     *   type="string", example="menu,category,variants"
     * )
     */
}
