<?php

namespace App\Http\Controllers\Model;

use App\Http\Controllers\CrudController;
use App\Http\Requests\Dish\IndexDishMenuRequest;
use App\Http\Requests\Dish\ShowDishMenuRequest;
use App\Http\Resources\Dish\DishMenuCollection;
use App\Http\Resources\Dish\DishMenuResource;
use App\Policies\DishMenuPolicy;
use App\Repositories\DishMenuRepository;

/**
 * Class DishMenuController.
 */
class DishMenuController extends CrudController
{
    /**
     * Controller's model resource class.
     *
     * @var string
     */
    protected string $resourceClass = DishMenuResource::class;

    /**
     * Controller's model resource collection class.
     *
     * @var string
     */
    protected string $collectionClass = DishMenuCollection::class;

    /**
     * DishMenuController constructor.
     *
     * @param DishMenuRepository $repository
     * @param DishMenuPolicy $policy
     */
    public function __construct(DishMenuRepository $repository, DishMenuPolicy $policy)
    {
        parent::__construct($repository, $policy);

        $this->actions['index'] = IndexDishMenuRequest::class;
        $this->actions['show'] = ShowDishMenuRequest::class;
    }

    /**
     * @OA\Get(
     *   path="/api/dishes/menus",
     *   summary="Index dish menus.",
     *   operationId="indexDishMenus",
     *   security={{"bearerAuth": {}}},
     *   tags={"dish.menus"},
     *
     *   @OA\Parameter(name="include", in="query",
     *     @OA\Schema(ref ="#/components/schemas/DishMenuIncludes")),
     *   @OA\Parameter(name="page[size]", in="query", @OA\Schema(ref ="#/components/schemas/PageSize")),
     *   @OA\Parameter(name="page[number]", in="query", @OA\Schema(ref ="#/components/schemas/PageNumber")),
     *   @OA\Parameter(name="sort", in="query", example="-popularity", @OA\Schema(type="string"),
     *        description="Available sorts: `popularity` (is default, but in descending order)"),
     *   @OA\Parameter(name="filter[restaurants]", required=false, in="query", example="1",
     *   @OA\Schema(type="string"), description="Coma-separated array of restaurant ids. Limits menus to those
     * that are attached at least to one of those restaurants"),
     *   @OA\Parameter(name="archived", in="query", @OA\Schema(ref ="#/components/schemas/ArchivedParameter")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Index dish menus response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/IndexDishMenuResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Get(
     *   path="/api/dishes/menus/{id}",
     *   summary="Show dish menu by id.",
     *   operationId="showDishMenu",
     *   security={{"bearerAuth": {}}},
     *   tags={"dish.menus"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the dish menu."),
     *  @OA\Parameter(name="include", in="query",
     *     @OA\Schema(ref ="#/components/schemas/DishMenuIncludes")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Show dish menu response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/ShowDishMenuResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     *
     * @OA\Schema(
     *   schema="IndexDishMenuResponse",
     *   description="Index dish menus response object.",
     *   required = {"data", "meta", "message"},
     *   @OA\Property(property="data", type="array", @OA\Items(ref ="#/components/schemas/DishMenu")),
     *   @OA\Property(property="meta", ref ="#/components/schemas/PaginationMeta"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="ShowDishMenuResponse",
     *   description="Show dish menu response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/DishMenu"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="DishMenuIncludes",
     *   description="Coma-separated list of inluded relations.
     *       Available relations: `dishes`, `categories`, `restaurant`, `media`",
     *   type="string", example="dishes,categories"
     * )
     */
}
