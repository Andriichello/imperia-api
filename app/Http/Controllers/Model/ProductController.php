<?php

namespace App\Http\Controllers\Model;

use App\Http\Controllers\CrudController;
use App\Http\Requests\CrudRequest;
use App\Http\Requests\Product\IndexProductRequest;
use App\Http\Requests\Product\ShowProductRequest;
use App\Http\Resources\Product\ProductCollection;
use App\Http\Resources\Product\ProductResource;
use App\Policies\ProductPolicy;
use App\Queries\ProductQueryBuilder;
use App\Repositories\ProductRepository;

/**
 * Class ProductController.
 */
class ProductController extends CrudController
{
    /**
     * Controller's model resource class.
     *
     * @var string
     */
    protected string $resourceClass = ProductResource::class;

    /**
     * Controller's model resource collection class.
     *
     * @var string
     */
    protected string $collectionClass = ProductCollection::class;

    /**
     * ProductController constructor.
     *
     * @param ProductRepository $repository
     * @param ProductPolicy $policy
     */
    public function __construct(ProductRepository $repository, ProductPolicy $policy)
    {
        parent::__construct($repository, $policy);

        $this->actions['index'] = IndexProductRequest::class;
        $this->actions['show'] = ShowProductRequest::class;
    }

    /**
     * @OA\Get(
     *   path="/api/products",
     *   summary="Index products.",
     *   operationId="indexProducts",
     *   security={{"bearerAuth": {}}},
     *   tags={"products"},
     *
     *   @OA\Parameter(name="include", in="query",
     *     @OA\Schema(ref ="#/components/schemas/ProductIncludes")),
     *   @OA\Parameter(name="page[size]", in="query", @OA\Schema(ref ="#/components/schemas/PageSize")),
     *   @OA\Parameter(name="page[number]", in="query", @OA\Schema(ref ="#/components/schemas/PageNumber")),
     *   @OA\Parameter(name="filter[title]", required=false, in="query", example="Mojito",
     *     @OA\Schema(type="string"), description="Can be used for searches. Is partial."),
     *   @OA\Parameter(name="filter[menus]", required=false, in="query", example="1,2",
     *     @OA\Schema(type="string"), description="Coma-separated array of menu ids. Limits products to those
     * that are attached at least to one of given menus"),
     *   @OA\Parameter(name="filter[categories]", required=false, in="query", example="2",
     *     @OA\Schema(type="string"), description="Coma-separated array of category ids. Limits products to those
     * that have at least one of given categories attached to them"),
     *   @OA\Parameter(name="filter[restaurants]", required=false, in="query", example="1",
     *   @OA\Schema(type="string"), description="Coma-separated array of restaurant ids. Limits products to those
     * that are attached at least to one of those restaurants"),
     *   @OA\Parameter(name="archived", in="query", @OA\Schema(ref ="#/components/schemas/ArchivedParameter")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Index products response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/IndexProductResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Get(
     *   path="/api/products/{id}",
     *   summary="Show product by id.",
     *   operationId="showProduct",
     *   security={{"bearerAuth": {}}},
     *   tags={"products"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the products."),
     *  @OA\Parameter(name="include", in="query",
     *     @OA\Schema(ref ="#/components/schemas/ProductIncludes")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Show products response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/ShowProductResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     *
     * @OA\Schema(
     *   schema="IndexProductResponse",
     *   description="Index products response object.",
     *   required = {"data", "meta", "message"},
     *   @OA\Property(property="data", type="array", @OA\Items(ref ="#/components/schemas/Product")),
     *   @OA\Property(property="meta", ref ="#/components/schemas/PaginationMeta"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="ShowProductResponse",
     *   description="Show products response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Product"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="ProductIncludes",
     *   description="Coma-separated list of inluded relations.
    Available relations: `categories`",
     *   type="string", example="categories"
     * )
     */
}
