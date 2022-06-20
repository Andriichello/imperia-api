<?php

namespace App\Http\Controllers\Model;

use App\Http\Controllers\CrudController;
use App\Http\Requests\Category\IndexCategoryRequest;
use App\Http\Requests\Category\ShowCategoryRequest;
use App\Http\Resources\Category\CategoryCollection;
use App\Http\Resources\Category\CategoryResource;
use App\Policies\CategoryPolicy;
use App\Repositories\CategoryRepository;

/**
 * Class CategoryController.
 */
class CategoryController extends CrudController
{
    /**
     * Controller's model resource class.
     *
     * @var string
     */
    protected string $resourceClass = CategoryResource::class;

    /**
     * Controller's model resource collection class.
     *
     * @var string
     */
    protected string $collectionClass = CategoryCollection::class;

    /**
     * CategoryController constructor.
     *
     * @param CategoryRepository $repository
     * @param CategoryPolicy $policy
     */
    public function __construct(CategoryRepository $repository, CategoryPolicy $policy)
    {
        parent::__construct($repository, $policy);

        $this->actions['index'] = IndexCategoryRequest::class;
        $this->actions['show'] = ShowCategoryRequest::class;
    }

    /**
     * @OA\Get(
     *   path="/api/categories",
     *   summary="Index categories.",
     *   operationId="indexCategories",
     *   security={{"bearerAuth": {}}},
     *   tags={"categories"},
     *
     *   @OA\Parameter(name="page[size]", in="query", @OA\Schema(ref ="#/components/schemas/PageSize")),
     *   @OA\Parameter(name="page[number]", in="query", @OA\Schema(ref ="#/components/schemas/PageNumber")),
     *  @OA\Parameter(name="filter[target]", in="query", example="products", @OA\Schema(type="string"),
     *     description="Target class morph slug. Examples: `products`, `tickets`, `services`, `spaces`"),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Index categories response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/IndexCategoryResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Get(
     *   path="/api/categories/{id}",
     *   summary="Show category by id.",
     *   operationId="showCategory",
     *   security={{"bearerAuth": {}}},
     *   tags={"categories"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the category."),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Show category response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/ShowCategoryResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     *
     * @OA\Schema(
     *   schema="IndexCategoryResponse",
     *   description="Index categories response object.",
     *   required = {"data", "meta", "message"},
     *   @OA\Property(property="data", type="array", @OA\Items(ref ="#/components/schemas/Category")),
     *   @OA\Property(property="meta", ref ="#/components/schemas/PaginationMeta"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="ShowCategoryResponse",
     *   description="Show category response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Category"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     */
}
