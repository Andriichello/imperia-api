<?php

namespace App\Http\Controllers\Model;

use App\Http\Controllers\CrudController;
use App\Http\Requests\Product\DestroyProductRequest;
use App\Http\Requests\Product\IndexProductRequest;
use App\Http\Requests\Product\ShowProductRequest;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\Product\ProductCollection;
use App\Http\Resources\Product\ProductResource;
use App\Policies\ProductPolicy;
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
        $this->actions['store'] = StoreProductRequest::class;
        $this->actions['update'] = UpdateProductRequest::class;
        $this->actions['destroy'] = DestroyProductRequest::class;
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
     *   @OA\Parameter(name="sort", in="query", example="-popularity", @OA\Schema(type="string"),
            description="Available sorts: `popularity` (is default, but in descending order)"),
     *   @OA\Parameter(name="filter[ids]", required=false, in="query", example="1,2,3",
     *     @OA\Schema(type="string"), description="Coma-separated list of product ids."),
     *   @OA\Parameter(name="filter[title]", required=false, in="query", example="Mojito",
     *     @OA\Schema(type="string"), description="Can be used for searches. Is partial."),
     *   @OA\Parameter(name="filter[menus]", required=false, in="query", example="1,2",
     *     @OA\Schema(type="string"), description="Coma-separated array of menu ids. Limits products to those
         that are attached at least to one of given menus"),
     *   @OA\Parameter(name="filter[categories]", required=false, in="query", example="2",
     *     @OA\Schema(type="string"), description="Coma-separated array of category ids. Limits products to those
         that have at least one of given categories attached to them"),
     *   @OA\Parameter(name="filter[tags]", required=false, in="query", example="1",
     *     @OA\Schema(type="string"), description="Coma-separated array of tag ids. Limits products to those
         that have at least one of given tags attached to them"),
     *   @OA\Parameter(name="filter[restaurants]", required=false, in="query", example="1",
     *   @OA\Schema(type="string"), description="Coma-separated array of restaurant ids. Limits products to those
         that are attached at least to one of those restaurants"),
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
     * @OA\Post(
     *   path="/api/products",
     *   summary="Store product.",
     *   operationId="storeProduct",
     *   security={{"bearerAuth": {}}},
     *   tags={"products"},
     *
     *  @OA\RequestBody(
     *     required=true,
     *     description="Store product request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/StoreProductRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Create product response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/StoreProductResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Patch(
     *   path="/api/products/{id}",
     *   summary="Update product.",
     *   operationId="updateProduct",
     *   security={{"bearerAuth": {}}},
     *   tags={"products"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the product."),
     *
     *  @OA\RequestBody(
     *     required=true,
     *     description="Update product request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/UpdateProductRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Update product response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/UpdateProductResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Delete(
     *   path="/api/products/{id}",
     *   summary="Delete product.",
     *   operationId="destroyProduct",
     *   security={{"bearerAuth": {}}},
     *   tags={"products"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the product."),
     *
     *  @OA\RequestBody(
     *     required=false,
     *     description="Delete product request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/DestroyRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Delete product response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/DestroyProductResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Post(
     *   path="/api/products/{id}/restore",
     *   summary="Restore product.",
     *   operationId="restoreProduct",
     *   security={{"bearerAuth": {}}},
     *   tags={"products"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the product."),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Restore product response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/RestoreProductResponse")
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
     *   schema="StoreProductResponse",
     *   description="Store product response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Product"),
     *   @OA\Property(property="message", type="string", example="Created"),
     * ),
     * @OA\Schema(
     *   schema="UpdateProductResponse",
     *   description="Update product response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Product"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="DestroyProductResponse",
     *   description="Delete product response object.",
     *   required = {"message"},
     *   @OA\Property(property="message", type="string", example="Deleted"),
     * ),
     * @OA\Schema(
     *   schema="RestoreProductResponse",
     *   description="Restore product response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Product"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     *
     * @OA\Schema(
     *   schema="ProductIncludes",
     *   description="Coma-separated list of inluded relations.
         Available relations: `categories`, `tags`,
            `alterations`, `variants.alterations`,
            `pendingAlterations`, `variants.pendingAlterations`,
            `performedAlterations`, `variants.performedAlterations`",
     *   type="string", example="categories,tags"
     * )
     */
}
