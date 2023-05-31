<?php

namespace App\Http\Controllers\Model;

use App\Http\Controllers\CrudController;
use App\Http\Requests\Banquet\IndexBanquetRequest;
use App\Http\Requests\Banquet\ShowBanquetRequest;
use App\Http\Requests\Banquet\StoreBanquetRequest;
use App\Http\Requests\Banquet\UpdateBanquetRequest;
use App\Http\Requests\CrudRequest;
use App\Http\Resources\Banquet\BanquetCollection;
use App\Http\Resources\Banquet\BanquetResource;
use App\Policies\BanquetPolicy;
use App\Queries\BanquetQueryBuilder;
use App\Repositories\BanquetRepository;
use OpenApi\Annotations as OA;

/**
 * Class BanquetController.
 */
class BanquetController extends CrudController
{
    /**
     * Controller's model resource class.
     *
     * @var string
     */
    protected string $resourceClass = BanquetResource::class;

    /**
     * Controller's model resource collection class.
     *
     * @var string
     */
    protected string $collectionClass = BanquetCollection::class;

    /**
     * BanquetController constructor.
     *
     * @param BanquetRepository $repository
     * @param BanquetPolicy $policy
     */
    public function __construct(BanquetRepository $repository, BanquetPolicy $policy)
    {
        parent::__construct($repository, $policy);

        $this->actions['index'] = IndexBanquetRequest::class;
        $this->actions['show'] = ShowBanquetRequest::class;
        $this->actions['store'] = StoreBanquetRequest::class;
        $this->actions['update'] = UpdateBanquetRequest::class;
    }

    /**
     * Get eloquent query builder instance.
     *
     * @param CrudRequest $request
     *
     * @return BanquetQueryBuilder
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function builder(CrudRequest $request): BanquetQueryBuilder
    {
        /** @var BanquetQueryBuilder $builder */
        $builder = parent::builder($request);

        return $builder->index($request->user());
    }

    /**
     * @OA\Get(
     *   path="/api/banquets",
     *   summary="Index banquets.",
     *   operationId="indexBanquets",
     *   security={{"bearerAuth": {}}},
     *   tags={"banquets"},
     *
     *   @OA\Parameter(name="include", in="query",
     *     @OA\Schema(ref ="#/components/schemas/BanquetIncludes")),
     *   @OA\Parameter(name="filter[search]", in="query", example="John", @OA\Schema(type="string"),
     *     description="Allows to search by banquet's `title` and customer's `name`, `surname`,
           `phone` and `email` at the same time"),
     *   @OA\Parameter(name="filter[restaurant_id]", in="query", example=1, @OA\Schema(type="integer")),
     *   @OA\Parameter(name="filter[state]", in="query", example="draft,new", @OA\Schema(type="string")),
     *   @OA\Parameter(name="filter[from]", in="query", example="2023-01-25",
     *     @OA\Schema(type="string", format="date")),
     *   @OA\Parameter(name="filter[until]", in="query", example="2023-12-25",
     *     @OA\Schema(type="string", format="date")),
     *   @OA\Parameter(name="page[size]", in="query", @OA\Schema(ref ="#/components/schemas/PageSize")),
     *   @OA\Parameter(name="page[number]", in="query", @OA\Schema(ref ="#/components/schemas/PageNumber")),
     *   @OA\Parameter(name="deleted", in="query",
     *     @OA\Schema(ref ="#/components/schemas/DeletedParameter")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Index banquets response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/IndexBanquetResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Get(
     *   path="/api/banquets/{id}",
     *   summary="Show banquets by id.",
     *   operationId="showBanquet",
     *   security={{"bearerAuth": {}}},
     *   tags={"banquets"},
     *
     *  @OA\Parameter(name="include", in="query",
     *     @OA\Schema(ref ="#/components/schemas/BanquetIncludes")),
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the banquet."),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Show banquet response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/ShowBanquetResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Post(
     *   path="/api/banquets",
     *   summary="Store banquet.",
     *   operationId="storeBanquet",
     *   security={{"bearerAuth": {}}},
     *   tags={"banquets"},
     *
     *  @OA\RequestBody(
     *     required=true,
     *     description="Store banquet request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/StoreBanquetRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Create banquet response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/StoreBanquetResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Patch(
     *   path="/api/banquets/{id}",
     *   summary="Update banquet.",
     *   operationId="updateBanquet",
     *   security={{"bearerAuth": {}}},
     *   tags={"banquets"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the banquet."),
     *  @OA\RequestBody(
     *     required=true,
     *     description="Update banquet request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/UpdateBanquetRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Update banquet response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/UpdateBanquetResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Delete(
     *   path="/api/banquets/{id}",
     *   summary="Delete banquet.",
     *   operationId="destroyBanquet",
     *   security={{"bearerAuth": {}}},
     *   tags={"banquets"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the banquet."),
     *
     *  @OA\RequestBody(
     *     required=false,
     *     description="Delete request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/DestroyRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Delete banquet response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/DestroyBanquetResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Post(
     *   path="/api/banquets/{id}/restore",
     *   summary="Restore banquet.",
     *   operationId="restoreBanquet",
     *   security={{"bearerAuth": {}}},
     *   tags={"banquets"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the banquet."),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Restore banquet response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/RestoreBanquetResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     *
     * @OA\Schema(
     *   schema="BanquetIncludes",
     *   description="Coma-separated list of inluded relations.
    Available relations: `creator`, `customer`, `comments`",
     *   type="string", example="creator,customer,comments"
     * ),
     * @OA\Schema(
     *   schema="IndexBanquetResponse",
     *   description="Index banquets response object.",
     *   required = {"data", "meta", "message"},
     *   @OA\Property(property="data", type="array", @OA\Items(ref ="#/components/schemas/Banquet")),
     *   @OA\Property(property="meta", ref ="#/components/schemas/PaginationMeta"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="ShowBanquetResponse",
     *   description="Show banquet response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Banquet"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="StoreBanquetResponse",
     *   description="Store banquet response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Banquet"),
     *   @OA\Property(property="message", type="string", example="Created"),
     * ),
     * @OA\Schema(
     *   schema="UpdateBanquetResponse",
     *   description="Update banquet response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Banquet"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="DestroyBanquetResponse",
     *   description="Delete banquet response object.",
     *   required = {"message"},
     *   @OA\Property(property="message", type="string", example="Deleted"),
     * ),
     * @OA\Schema(
     *   schema="RestoreBanquetResponse",
     *   description="Restore banquet response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Banquet"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     */
}
