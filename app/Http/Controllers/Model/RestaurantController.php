<?php

namespace App\Http\Controllers\Model;

use App\Http\Controllers\CrudController;
use App\Http\Requests\Restaurant\IndexRestaurantRequest;
use App\Http\Requests\Restaurant\ShowRestaurantRequest;
use App\Http\Resources\Restaurant\RestaurantCollection;
use App\Http\Resources\Restaurant\RestaurantResource;
use App\Policies\RestaurantPolicy;
use App\Repositories\RestaurantRepository;

/**
 * Class RestaurantController.
 */
class RestaurantController extends CrudController
{
    /**
     * Controller's model resource class.
     *
     * @var string
     */
    protected string $resourceClass = RestaurantResource::class;

    /**
     * Controller's model resource collection class.
     *
     * @var string
     */
    protected string $collectionClass = RestaurantCollection::class;

    /**
     * RestaurantController constructor.
     *
     * @param RestaurantRepository $repository
     * @param RestaurantPolicy $policy
     */
    public function __construct(RestaurantRepository $repository, RestaurantPolicy $policy)
    {
        parent::__construct($repository, $policy);

        $this->actions['index'] = IndexRestaurantRequest::class;
        $this->actions['show'] = ShowRestaurantRequest::class;
    }

    /**
     * @OA\Get(
     *   path="/api/restaurants",
     *   summary="Index restaurants.",
     *   operationId="indexRestaurants",
     *   security={{"bearerAuth": {}}},
     *   tags={"restaurants"},
     *
     *  @OA\Parameter(name="filter[slug]", in="query", example="first", @OA\Schema(type="string")),
     *  @OA\Parameter(name="filter[name]", in="query", example="First", @OA\Schema(type="string")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Index restaurants response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/IndexRestaurantResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Get(
     *   path="/api/restaurants/{id}",
     *   summary="Show restaurant by id.",
     *   operationId="showRestaurant",
     *   security={{"bearerAuth": {}}},
     *   tags={"restaurants"},
     *
     *   @OA\Response(
     *     response=200,
     *     description="Show restaurant response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/ShowRestaurantResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     *
     * @OA\Schema(
     *   schema="IndexRestaurantResponse",
     *   description="Index restaurant response object.",
     *   required = {"data", "meta", "message"},
     *   @OA\Property(property="data", type="array", @OA\Items(ref ="#/components/schemas/Restaurant")),
     *   @OA\Property(property="meta", ref ="#/components/schemas/PaginationMeta"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="ShowRestaurantResponse",
     *   description="Show restaurant response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Restaurant"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     */
}
