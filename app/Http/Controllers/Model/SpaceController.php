<?php

namespace App\Http\Controllers\Model;

use App\Http\Controllers\CrudController;
use App\Http\Requests\CrudRequest;
use App\Http\Requests\Space\IndexSpaceRequest;
use App\Http\Requests\Space\ShowSpaceRequest;
use App\Http\Requests\Space\SpaceReservationsRequest;
use App\Http\Resources\Field\SpaceReservationCollection;
use App\Http\Resources\ResourcePaginator;
use App\Http\Resources\Space\SpaceCollection;
use App\Http\Resources\Space\SpaceResource;
use App\Models\Orders\SpaceOrderField;
use App\Policies\SpacePolicy;
use App\Queries\SpaceQueryBuilder;
use App\Repositories\SpaceRepository;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use OpenApi\Annotations as OA;

/**
 * Class SpaceController.
 */
class SpaceController extends CrudController
{
    /**
     * Controller's model resource class.
     *
     * @var string
     */
    protected string $resourceClass = SpaceResource::class;

    /**
     * Controller's model resource collection class.
     *
     * @var string
     */
    protected string $collectionClass = SpaceCollection::class;

    /**
     * SpaceController constructor.
     *
     * @param SpaceRepository $repository
     * @param SpacePolicy $policy
     */
    public function __construct(SpaceRepository $repository, SpacePolicy $policy)
    {
        parent::__construct($repository, $policy);

        $this->actions['index'] = IndexSpaceRequest::class;
        $this->actions['show'] = ShowSpaceRequest::class;
    }

    /**
     * Get space reservations for given interval of time.
     *
     * @param SpaceReservationsRequest $request
     *
     * @return ResourcePaginator
     * @throws Exception
     */
    public function reservations(SpaceReservationsRequest $request): ResourcePaginator
    {
        $orderId = $request->get('order_id');
        $builder = SpaceOrderField::query()
            ->between($request->getStartAt(), $request->getEndAt())
            ->when($orderId, function (Builder $query) use ($orderId) {
                $query->where('order_id', '!=', $orderId);
            });

        return $this->paginateResource($builder, SpaceReservationCollection::class);
    }

    /**
     * @OA\Get(
     *   path="/api/spaces",
     *   summary="Index spaces.",
     *   operationId="indexSpaces",
     *   security={{"bearerAuth": {}}},
     *   tags={"spaces"},
     *
     *   @OA\Parameter(name="include", in="query",
     *     @OA\Schema(ref ="#/components/schemas/SpaceIncludes")),
     *   @OA\Parameter(name="page[size]", in="query", @OA\Schema(ref ="#/components/schemas/PageSize")),
     *   @OA\Parameter(name="page[number]", in="query", @OA\Schema(ref ="#/components/schemas/PageNumber")),
     *   @OA\Parameter(name="sort", in="query", example="-popularity", @OA\Schema(type="string"),
            description="Available sorts: `popularity` (is default, but in descending order)"),
     *   @OA\Parameter(name="filter[ids]", required=false, in="query", example="1,2,3",
     *     @OA\Schema(type="string"), description="Coma-separated list of space ids."),
     *   @OA\Parameter(name="filter[title]", required=false, in="query", example="#1",
     *     @OA\Schema(type="string"), description="Can be used for searches. Is partial."),
     *   @OA\Parameter(name="filter[categories]", required=false, in="query", example="2,3",
     *     @OA\Schema(type="string"), description="Coma-separated array of category ids. Limits spaces to those
     * that have at least one of given categories attached to them"),
     *   @OA\Parameter(name="filter[restaurants]", required=false, in="query", example="1",
     *   @OA\Schema(type="string"), description="Coma-separated array of restaurant ids. Limits spaces to those
     * that are attached at least to one of those restaurants"),
     *   @OA\Parameter(name="archived", in="query", @OA\Schema(ref ="#/components/schemas/ArchivedParameter")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Index spaces response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/IndexSpaceResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Get(
     *   path="/api/spaces/{id}",
     *   summary="Show space by id.",
     *   operationId="showSpace",
     *   security={{"bearerAuth": {}}},
     *   tags={"spaces"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the spaces."),
     *  @OA\Parameter(name="include", in="query",
     *     @OA\Schema(ref ="#/components/schemas/SpaceIncludes")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Show spaces response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/ShowSpaceResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Get(
     *   path="/api/spaces/reservations",
     *   summary="Index spaces reservations.",
     *   operationId="indexSpacesReservations",
     *   security={{"bearerAuth": {}}},
     *   tags={"spaces"},
     *
     *  @OA\Parameter(name="order_id", in="query", required=false,
     *     description="Id of current banquet's order (do not specify if order is just creating).",
     *     @OA\Schema(type="integer", example="")),
     *  @OA\Parameter(name="start_at", in="query", required=true,
     *     @OA\Schema(type="string", format="date-time", example="2022-01-12 11:00:00")),
     *  @OA\Parameter(name="end_at", in="query", required=false,
     *     description="Must be after or equal to `start_at`. If not present then `start_at` will be taken.",
     *     @OA\Schema(type="string", format="date-time", example="2022-01-12 13:00:00")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Index spaces reservations response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/IndexSpaceReservationsResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     *
     * @OA\Schema(
     *   schema="IndexSpaceResponse",
     *   description="Index spaces response object.",
     *   required = {"data", "meta", "message"},
     *   @OA\Property(property="data", type="array", @OA\Items(ref ="#/components/schemas/Space")),
     *   @OA\Property(property="meta", ref ="#/components/schemas/PaginationMeta"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="ShowSpaceResponse",
     *   description="Show spaces response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Space"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="IndexSpaceReservationsResponse",
     *   description="Index spaces reservations response object.",
     *   required = {"data", "meta", "message"},
     *   @OA\Property(property="data", type="array", @OA\Items(ref ="#/components/schemas/SpaceReservation")),
     *   @OA\Property(property="meta", ref ="#/components/schemas/PaginationMeta"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     *
     * @OA\Schema(
     *   schema="SpaceIncludes",
     *   description="Coma-separated list of inluded relations.
    Available relations: `categories`",
     *   type="string", example="categories"
     * )
     */
}
