<?php

namespace App\Http\Controllers\Model;

use App\Http\Controllers\CrudController;
use App\Http\Requests\RestaurantReview\IndexRestaurantReviewRequest;
use App\Http\Requests\RestaurantReview\ShowRestaurantReviewRequest;
use App\Http\Requests\RestaurantReview\StoreRestaurantReviewRequest;
use App\Http\Resources\Restaurant\RestaurantReviewCollection;
use App\Http\Resources\Restaurant\RestaurantReviewResource;
use App\Policies\RestaurantReviewPolicy;
use App\Repositories\RestaurantReviewRepository;
use OpenApi\Annotations as OA;

/**
 * Class RestaurantReviewController.
 */
class RestaurantReviewController extends CrudController
{
    /**
     * Controller's model resource class.
     *
     * @var string
     */
    protected string $resourceClass = RestaurantReviewResource::class;

    /**
     * Controller's model resource collection class.
     *
     * @var string
     */
    protected string $collectionClass = RestaurantReviewCollection::class;

    /**
     * RestaurantReviewController constructor.
     *
     * @param RestaurantReviewRepository $repository
     * @param RestaurantReviewPolicy $policy
     */
    public function __construct(
        RestaurantReviewRepository $repository,
        RestaurantReviewPolicy $policy
    ) {
        parent::__construct($repository, $policy);

        $this->actions['index'] = IndexRestaurantReviewRequest::class;
        $this->actions['show'] = ShowRestaurantReviewRequest::class;
        $this->actions['store'] = StoreRestaurantReviewRequest::class;
    }

    /**
     * @OA\Get(
     *   path="/api/restaurant-reviews",
     *   summary="Index restaurant reviews.",
     *   operationId="indexRestaurantReviews",
     *   security={{"bearerAuth": {}}},
     *   tags={"restaurant-reviews"},
     *
     *   @OA\Parameter(name="page[size]", in="query", @OA\Schema(ref ="#/components/schemas/PageSize")),
     *   @OA\Parameter(name="page[number]", in="query", @OA\Schema(ref ="#/components/schemas/PageNumber")),
     *   @OA\Parameter(name="sort", in="query", example="-created_at", @OA\Schema(type="string"),
            description="Available sorts: `created_at` (is default, but in descending order)"),
     *   @OA\Parameter(name="filter[restaurant_id]", required=false, in="query",
     *     example="1", @OA\Schema(type="string")),
     *   @OA\Parameter(name="filter[ip]", required=false, in="query",
     *     example="127.0.0.1", @OA\Schema(type="string")),
     *   @OA\Parameter(name="is_approved", required=false, in="query",
     *     @OA\Schema(type="boolean", example="true")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Index restaurant review response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/IndexRestaurantReviewResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Get(
     *   path="/api/restaurant-reviews/{id}",
     *   summary="Show restaurant reviews by id.",
     *   operationId="showRestaurantReview",
     *   security={{"bearerAuth": {}}},
     *   tags={"restaurant-reviews"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the restaurant review."),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Show restaurant review response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/ShowRestaurantReviewResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Post(
     *   path="/api/restaurant-reviews",
     *   summary="Store banquet.",
     *   operationId="storeRestaurantReview",
     *   security={{"bearerAuth": {}}},
     *   tags={"restaurant-reviews"},
     *
     *  @OA\RequestBody(
     *     required=true,
     *     description="Store restaurant review request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/StoreRestaurantReviewRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Create restaurant review response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/StoreRestaurantReviewResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     *
     * @OA\Schema(
     *   schema="IndexRestaurantReviewResponse",
     *   description="Index restaurant reviews response object.",
     *   required = {"data", "meta", "message"},
     *   @OA\Property(property="data", type="array", @OA\Items(ref ="#/components/schemas/RestaurantReview")),
     *   @OA\Property(property="meta", ref ="#/components/schemas/PaginationMeta"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="ShowRestaurantReviewResponse",
     *   description="Show restaurant review response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/RestaurantReview"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="StoreRestaurantReviewResponse",
     *   description="Store restaurant review response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/RestaurantReview"),
     *   @OA\Property(property="message", type="string", example="Created"),
     * ),
     */
}
