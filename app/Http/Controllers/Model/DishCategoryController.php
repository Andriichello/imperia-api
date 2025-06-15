<?php

namespace App\Http\Controllers\Model;

use App\Http\Controllers\CrudController;
use App\Http\Requests\Dish\IndexDishCategoryRequest;
use App\Http\Requests\Dish\ShowDishCategoryRequest;
use App\Http\Resources\Dish\DishCategoryCollection;
use App\Http\Resources\Dish\DishCategoryResource;
use App\Policies\DishCategoryPolicy;
use App\Repositories\DishCategoryRepository;

/**
 * Class DishCategoryController.
 */
class DishCategoryController extends CrudController
{
    /**
     * Controller's model resource class.
     *
     * @var string
     */
    protected string $resourceClass = DishCategoryResource::class;

    /**
     * Controller's model resource collection class.
     *
     * @var string
     */
    protected string $collectionClass = DishCategoryCollection::class;

    /**
     * DishCategoryController constructor.
     *
     * @param DishCategoryRepository $repository
     * @param DishCategoryPolicy $policy
     */
    public function __construct(DishCategoryRepository $repository, DishCategoryPolicy $policy)
    {
        parent::__construct($repository, $policy);

        $this->actions['index'] = IndexDishCategoryRequest::class;
        $this->actions['show'] = ShowDishCategoryRequest::class;
    }

    /**
     * @OA\Get(
     *   path="/api/dishes/categories",
     *   summary="Index dish categories.",
     *   operationId="indexDishCategories",
     *   security={{"bearerAuth": {}}},
     *   tags={"dish.categories"},
     *
     *   @OA\Parameter(name="include", in="query",
     *     @OA\Schema(ref ="#/components/schemas/DishCategoryIncludes")),
     *   @OA\Parameter(name="page[size]", in="query", @OA\Schema(ref ="#/components/schemas/PageSize")),
     *   @OA\Parameter(name="page[number]", in="query", @OA\Schema(ref ="#/components/schemas/PageNumber")),
     *   @OA\Parameter(name="sort", in="query", example="-popularity", @OA\Schema(type="string"),
     *        description="Available sorts: `popularity` (is default, but in descending order)"),
     *   @OA\Parameter(name="filter[menu_id]", required=false, in="query", example="1",
     *     @OA\Schema(type="string"), description="Menu ID to filter categories by."),
     *   @OA\Parameter(name="filter[target]", in="query", example="dishes", @OA\Schema(type="string"),
     *     description="Target class morph slug. Examples: `dishes`"),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Index dish categories response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/IndexDishCategoryResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Get(
     *   path="/api/dishes/categories/{id}",
     *   summary="Show dish category by id.",
     *   operationId="showDishCategory",
     *   security={{"bearerAuth": {}}},
     *   tags={"dish.categories"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the dish category."),
     *  @OA\Parameter(name="include", in="query",
     *    @OA\Schema(ref ="#/components/schemas/DishCategoryIncludes")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Show dish category response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/ShowDishCategoryResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     *
     * @OA\Schema(
     *   schema="IndexDishCategoryResponse",
     *   description="Index dish categories response object.",
     *   required = {"data", "meta", "message"},
     *   @OA\Property(property="data", type="array", @OA\Items(ref ="#/components/schemas/DishCategory")),
     *   @OA\Property(property="meta", ref ="#/components/schemas/PaginationMeta"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="ShowDishCategoryResponse",
     *   description="Show dish category response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/DishCategory"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     *
     * @OA\Schema(
     *   schema="DishCategoryIncludes",
     *   description="Coma-separated list of inluded relations.
     *       Available relations: `menu`, `media`",
     *   type="string", example="menu"
     * )
     */
}
