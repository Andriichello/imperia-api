<?php

namespace App\Http\Controllers\Model;

use App\Http\Controllers\CrudController;
use App\Http\Requests\Restaurant\GetHolidaysRequest;
use App\Http\Requests\Restaurant\GetSchedulesRequest;
use App\Http\Requests\Restaurant\IndexRestaurantRequest;
use App\Http\Requests\Restaurant\ShowRestaurantRequest;
use App\Http\Resources\Holiday\HolidayCollection;
use App\Http\Resources\Restaurant\RestaurantCollection;
use App\Http\Resources\Restaurant\RestaurantResource;
use App\Http\Resources\Schedule\ScheduleCollection;
use App\Http\Responses\ApiResponse;
use App\Models\Restaurant;
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
     * Get restaurant's schedules.
     *
     * @param GetSchedulesRequest $request
     *
     * @return ApiResponse
     */
    public function getSchedules(GetSchedulesRequest $request): ApiResponse
    {
        /** @var Restaurant $restaurant */
        $restaurant = $request->targetOrFail(Restaurant::class);
        $data = new ScheduleCollection($restaurant->schedules);

        return ApiResponse::make(compact('data'));
    }

    /**
     * Get restaurant's holidays.
     *
     * @param GetHolidaysRequest $request
     *
     * @return ApiResponse
     */
    public function getHolidays(GetHolidaysRequest $request): ApiResponse
    {
        /** @var Restaurant $restaurant */
        $restaurant = $request->targetOrFail(Restaurant::class);

        $query = $restaurant->holidays()
            ->where('date', '>=', $request->date('from'))
            ->limit(25);

        $data = new HolidayCollection($query->get());

        return ApiResponse::make(compact('data'));
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
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the restaurant."),
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
     * @OA\Get(
     *   path="/api/restaurants/{id}/schedules",
     *   summary="Get restaurant's schedules.",
     *   operationId="getSchedules",
     *   security={{"bearerAuth": {}}},
     *   tags={"restaurants"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the restaurant."),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Get restaurant's schedules response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/GetSchedulesResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Get(
     *   path="/api/restaurants/{id}/holidays",
     *   summary="Get restaurant's holidays.",
     *   operationId="getHolidays",
     *   security={{"bearerAuth": {}}},
     *   tags={"restaurants"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the restaurant."),
     *  @OA\Parameter(name="days", in="query", example=1, @OA\Schema(type="string"),
     *     description="Number of days, for which holidays should be returned."),
     *  @OA\Parameter(name="from", in="query", example="2022-12-25",
     *     @OA\Schema(type="string", format="date"),
     *     description="Date starting from which holidays should be returned."),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Show restaurant's holidays response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/GetHolidaysResponse")
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
     * @OA\Schema(
     *   schema="GetSchedulesResponse",
     *   description="Get restaurant's schedules response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", type="array", @OA\Items(ref ="#/components/schemas/Schedule")),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="GetHolidaysResponse",
     *   description="Get restaurant's holidays response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", type="array", @OA\Items(ref ="#/components/schemas/Holiday")),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     */
}
