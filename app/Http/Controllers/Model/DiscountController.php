<?php

namespace App\Http\Controllers\Model;

use App\Http\Controllers\CrudController;
use App\Http\Requests\Discount\IndexDiscountRequest;
use App\Http\Requests\Discount\ShowDiscountRequest;
use App\Http\Resources\Discount\DiscountCollection;
use App\Http\Resources\Discount\DiscountResource;
use App\Policies\DiscountPolicy;
use App\Repositories\DiscountRepository;
use OpenApi\Annotations as OA;

/**
 * Class DiscountController.
 */
class DiscountController extends CrudController
{
    /**
     * Controller's model resource class.
     *
     * @var string
     */
    protected string $resourceClass = DiscountResource::class;

    /**
     * Controller's model resource collection class.
     *
     * @var string
     */
    protected string $collectionClass = DiscountCollection::class;

    /**
     * DiscountController constructor.
     *
     * @param DiscountRepository $repository
     * @param DiscountPolicy $policy
     */
    public function __construct(DiscountRepository $repository, DiscountPolicy $policy)
    {
        parent::__construct($repository, $policy);

        $this->actions['index'] = IndexDiscountRequest::class;
        $this->actions['show'] = ShowDiscountRequest::class;
    }

    /**
     * @OA\Get(
     *   path="/api/discounts",
     *   summary="Index discounts.",
     *   operationId="indexDiscounts",
     *   security={{"bearerAuth": {}}},
     *   tags={"discounts"},
     *
     *   @OA\Parameter(name="page[size]", in="query", @OA\Schema(ref ="#/components/schemas/PageSize")),
     *   @OA\Parameter(name="page[number]", in="query", @OA\Schema(ref ="#/components/schemas/PageNumber")),
     *  @OA\Parameter(name="filter[target]", in="query", example="products", @OA\Schema(type="string"),
     *     description="Target class morph slug. Examples: `products`, `tickets`, `services`, `spaces`"),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Index discounts response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/IndexDiscountResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Get(
     *   path="/api/discounts/{id}",
     *   summary="Show discount by id.",
     *   operationId="showDiscount",
     *   security={{"bearerAuth": {}}},
     *   tags={"discounts"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the discount."),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Show discount response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/ShowDiscountResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     *
     * @OA\Schema(
     *   schema="IndexDiscountResponse",
     *   description="Index discounts response object.",
     *   required = {"data", "meta", "message"},
     *   @OA\Property(property="data", type="array", @OA\Items(ref ="#/components/schemas/Discount")),
     *   @OA\Property(property="meta", ref ="#/components/schemas/PaginationMeta"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="ShowDiscountResponse",
     *   description="Show discount response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Discount"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     */
}
