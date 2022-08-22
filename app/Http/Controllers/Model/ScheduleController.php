<?php

namespace App\Http\Controllers\Model;

use App\Http\Controllers\CrudController;
use App\Http\Requests\Schedule\IndexScheduleRequest;
use App\Http\Requests\Schedule\ShowScheduleRequest;
use App\Http\Resources\Schedule\ScheduleCollection;
use App\Http\Resources\Schedule\ScheduleResource;
use App\Http\Responses\ApiResponse;
use App\Models\Restaurant;
use App\Models\Schedule;
use App\Policies\SchedulePolicy;
use App\Queries\ScheduleQueryBuilder;
use App\Repositories\ScheduleRepository;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class ScheduleController.
 */
class ScheduleController extends CrudController
{
    /**
     * Controller's model resource class.
     *
     * @var string
     */
    protected string $resourceClass = ScheduleResource::class;

    /**
     * Controller's model resource collection class.
     *
     * @var string
     */
    protected string $collectionClass = ScheduleCollection::class;

    /**
     * ScheduleController constructor.
     *
     * @param ScheduleRepository $repository
     * @param SchedulePolicy $policy
     */
    public function __construct(ScheduleRepository $repository, SchedulePolicy $policy)
    {
        parent::__construct($repository, $policy);

        $this->actions['index'] = IndexScheduleRequest::class;
        $this->actions['show'] = ShowScheduleRequest::class;
    }

    /**
     * Index schedules for all week or a specific weekday.
     *
     * @param IndexScheduleRequest $request
     *
     * @return ApiResponse
     */
    public function index(IndexScheduleRequest $request): ApiResponse
    {
        /** @var ScheduleQueryBuilder $builder */
        $builder = $this->builder($request);

        $weekday = data_get($request->get('filter'), 'weekday');
        $restaurantId = data_get($request->get('filter'), 'restaurant_id');

        if (!$restaurantId) {
            $schedules = $builder->onlyDefaults()
                ->when($weekday, fn(Builder $query) => $query->where('weekday', $weekday))
                ->get();

            return ApiResponse::make(['data' => new ScheduleCollection($schedules)]);
        }

        /** @var Restaurant $restaurant */
        $restaurant = Restaurant::query()
            ->findOrFail($restaurantId);

        $schedules = $weekday ? $restaurant->operativeSchedules($weekday)
            : $restaurant->operativeSchedules();

        return ApiResponse::make(['data' => new ScheduleCollection($schedules)]);
    }

    /**
     * @OA\Get(
     *   path="/api/schedules",
     *   summary="Index schedules.",
     *   operationId="indexSchedules",
     *   security={{"bearerAuth": {}}},
     *   tags={"schedules"},
     *
     *  @OA\Parameter(name="filter[weekday]", in="query", example="monday", @OA\Schema(type="string",
     *     enum={"monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday"})),
     *  @OA\Parameter(name="filter[restaurant_id]", in="query", example=1, @OA\Schema(type="integer")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Index schedules response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/IndexScheduleResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Get(
     *   path="/api/schedules/{id}",
     *   summary="Show schedule by id.",
     *   operationId="showSchedule",
     *   security={{"bearerAuth": {}}},
     *   tags={"schedules"},
     *
     *   @OA\Response(
     *     response=200,
     *     description="Show schedule response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/ShowScheduleResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Schema(
     *   schema="IndexScheduleResponse",
     *   description="Index schedules response object.",
     *   required = {"data", "meta", "message"},
     *   @OA\Property(property="data", type="array", @OA\Items(ref ="#/components/schemas/Schedule")),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="ShowScheduleResponse",
     *   description="Show customer response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Schedule"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     */
}
