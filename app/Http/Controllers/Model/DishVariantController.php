<?php

namespace App\Http\Controllers\Model;

use App\Http\Controllers\CrudController;
use App\Http\Requests\Dish\IndexDishVariantRequest;
use App\Http\Requests\Dish\ShowDishVariantRequest;
use App\Http\Resources\Dish\DishVariantCollection;
use App\Http\Resources\Dish\DishVariantResource;
use App\Policies\DishVariantPolicy;
use App\Repositories\DishVariantRepository;

/**
 * Class DishVariantController.
 */
class DishVariantController extends CrudController
{
    /**
     * Controller's model resource class.
     *
     * @var string
     */
    protected string $resourceClass = DishVariantResource::class;

    /**
     * Controller's model resource collection class.
     *
     * @var string
     */
    protected string $collectionClass = DishVariantCollection::class;

    /**
     * DishVariantController constructor.
     *
     * @param DishVariantRepository $repository
     * @param DishVariantPolicy $policy
     */
    public function __construct(DishVariantRepository $repository, DishVariantPolicy $policy)
    {
        parent::__construct($repository, $policy);

        $this->actions['index'] = IndexDishVariantRequest::class;
        $this->actions['show'] = ShowDishVariantRequest::class;
    }

    /**
     * @OA\Get(
     *   path="/api/dishes/variants",
     *   summary="Index dish variants.",
     *   operationId="indexDishVariants",
     *   security={{"bearerAuth": {}}},
     *   tags={"dish.variants"},
     *
     *   @OA\Parameter(name="include", in="query",
     *     @OA\Schema(ref ="#/components/schemas/DishVariantIncludes")),
     *   @OA\Parameter(name="page[size]", in="query", @OA\Schema(ref ="#/components/schemas/PageSize")),
     *   @OA\Parameter(name="page[number]", in="query", @OA\Schema(ref ="#/components/schemas/PageNumber")),
     *   @OA\Parameter(name="sort", in="query", example="price", @OA\Schema(type="string"),
     *        description="Available sorts: `price` (is default)"),
     *   @OA\Parameter(name="filter[ids]", required=false, in="query", example="1,2,3",
     *     @OA\Schema(type="string"), description="Coma-separated list of dish variant ids."),
     *   @OA\Parameter(name="filter[dish_id]", required=false, in="query", example="1",
     *     @OA\Schema(type="string"), description="Dish ID to filter variants by."),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Index dish variants response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/IndexDishVariantResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Get(
     *   path="/api/dishes/variants/{id}",
     *   summary="Show dish variant by id.",
     *   operationId="showDishVariant",
     *   security={{"bearerAuth": {}}},
     *   tags={"dish.variants"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the dish variant."),
     *  @OA\Parameter(name="include", in="query",
     *    @OA\Schema(ref ="#/components/schemas/DishVariantIncludes")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Show dish variant response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/ShowDishVariantResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     *
     * @OA\Schema(
     *   schema="IndexDishVariantResponse",
     *   description="Index dish variants response object.",
     *   required = {"data", "meta", "message"},
     *   @OA\Property(property="data", type="array", @OA\Items(ref ="#/components/schemas/DishVariant")),
     *   @OA\Property(property="meta", ref ="#/components/schemas/PaginationMeta"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="ShowDishVariantResponse",
     *   description="Show dish variant response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/DishVariant"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     *
     * @OA\Schema(
     *   schema="DishVariantIncludes",
     *   description="Coma-separated list of inluded relations.
     *       Available relations: `dish`",
     *   type="string", example="dish"
     * )
     */
}
