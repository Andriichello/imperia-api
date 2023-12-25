<?php

namespace App\Http\Controllers\Model;

use App\Http\Controllers\CrudController;
use App\Http\Requests\CrudRequest;
use App\Http\Requests\Waiter\IndexWaiterRequest;
use App\Http\Requests\Waiter\ShowWaiterRequest;
use App\Http\Requests\Waiter\StoreWaiterRequest;
use App\Http\Requests\Waiter\UpdateWaiterRequest;
use App\Http\Resources\Waiter\WaiterCollection;
use App\Http\Resources\Waiter\WaiterResource;
use App\Policies\WaiterPolicy;
use App\Queries\WaiterQueryBuilder;
use App\Repositories\WaiterRepository;
use OpenApi\Annotations as OA;

/**
 * Class WaiterController.
 */
class WaiterController extends CrudController
{
    /**
     * Controller's model resource class.
     *
     * @var string
     */
    protected string $resourceClass = WaiterResource::class;

    /**
     * Controller's model resource collection class.
     *
     * @var string
     */
    protected string $collectionClass = WaiterCollection::class;

    /**
     * WaiterController constructor.
     *
     * @param WaiterRepository $repository
     * @param WaiterPolicy $policy
     */
    public function __construct(WaiterRepository $repository, WaiterPolicy $policy)
    {
        parent::__construct($repository, $policy);

        $this->actions['index'] = IndexWaiterRequest::class;
        $this->actions['show'] = ShowWaiterRequest::class;
        $this->actions['store'] = StoreWaiterRequest::class;
        $this->actions['update'] = UpdateWaiterRequest::class;
    }

    /**
     * Get eloquent query builder instance.
     *
     * @param CrudRequest $request
     *
     * @return WaiterQueryBuilder
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function builder(CrudRequest $request): WaiterQueryBuilder
    {
        /** @var WaiterQueryBuilder $builder */
        $builder = parent::builder($request);

        return $builder->index($request->user());
    }

    /**
     * @OA\Get(
     *   path="/api/waiters",
     *   summary="Index waiters.",
     *   operationId="indexWaiters",
     *   security={{"bearerAuth": {}}},
     *   tags={"waiters"},
     *
     *   @OA\Parameter(name="filter[restaurants]", required=false, in="query", example="1",
     *      @OA\Schema(type="string"), description="Coma-separated array of restaurant ids. Limits categories to those
            that are attached at least to one of those restaurants"),
     *   @OA\Parameter(name="filter[search]", in="query", example="John", @OA\Schema(type="string"),
     *     description="Allows to search `uuid`, `name`, `surname`, `phone` and `email` fields at the same time."),
     *   @OA\Parameter(name="filter[uuid]", in="query", example="John", @OA\Schema(type="string")),
     *   @OA\Parameter(name="filter[name]", in="query", example="John", @OA\Schema(type="string")),
     *   @OA\Parameter(name="filter[surname]", in="query", example="Doe", @OA\Schema(type="string")),
     *   @OA\Parameter(name="filter[phone]", in="query", example="+38050", @OA\Schema(type="string")),
     *   @OA\Parameter(name="filter[email]", in="query", example="john.doe@email.com", @OA\Schema(type="string")),
     *   @OA\Parameter(name="page[size]", in="query", @OA\Schema(ref ="#/components/schemas/PageSize")),
     *   @OA\Parameter(name="page[number]", in="query", @OA\Schema(ref ="#/components/schemas/PageNumber")),
     *   @OA\Parameter(name="deleted", in="query",
     *     @OA\Schema(ref ="#/components/schemas/DeletedParameter")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Index waiters response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/IndexWaiterResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Get(
     *   path="/api/waiters/{id}",
     *   summary="Show waiter by id.",
     *   operationId="showWaiter",
     *   security={{"bearerAuth": {}}},
     *   tags={"waiters"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the waiter."),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Show waiter response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/ShowWaiterResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Post(
     *   path="/api/waiters",
     *   summary="Store waiter.",
     *   operationId="storeWaiter",
     *   security={{"bearerAuth": {}}},
     *   tags={"waiters"},
     *
     *  @OA\RequestBody(
     *     required=true,
     *     description="Store waiter request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/StoreWaiterRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Create waiter response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/StoreWaiterResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Patch(
     *   path="/api/waiters/{id}",
     *   summary="Update waiter.",
     *   operationId="updateWaiter",
     *   security={{"bearerAuth": {}}},
     *   tags={"waiters"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the waiter."),
     *
     *  @OA\RequestBody(
     *     required=true,
     *     description="Update waiter request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/UpdateWaiterRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Update waiter response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/UpdateWaiterResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Delete(
     *   path="/api/waiters/{id}",
     *   summary="Delete waiter.",
     *   operationId="destroyWaiter",
     *   security={{"bearerAuth": {}}},
     *   tags={"waiters"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the waiter."),
     *
     *  @OA\RequestBody(
     *     required=false,
     *     description="Delete waiter request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/DestroyRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Delete waiter response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/DestroyWaiterResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Post(
     *   path="/api/waiters/{id}/restore",
     *   summary="Restore waiter.",
     *   operationId="restoreWaiter",
     *   security={{"bearerAuth": {}}},
     *   tags={"waiters"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the waiter."),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Restore waiter response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/RestoreWaiterResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     *
     * @OA\Schema(
     *   schema="IndexWaiterResponse",
     *   description="Index waiters response object.",
     *   required = {"data", "meta", "message"},
     *   @OA\Property(property="data", type="array", @OA\Items(ref ="#/components/schemas/Waiter")),
     *   @OA\Property(property="meta", ref ="#/components/schemas/PaginationMeta"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="ShowWaiterResponse",
     *   description="Show waiter response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Waiter"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="StoreWaiterResponse",
     *   description="Store waiter response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Waiter"),
     *   @OA\Property(property="message", type="string", example="Created"),
     * ),
     * @OA\Schema(
     *   schema="UpdateWaiterResponse",
     *   description="Update waiter response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Waiter"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="DestroyWaiterResponse",
     *   description="Delete waiter response object.",
     *   required = {"message"},
     *   @OA\Property(property="message", type="string", example="Deleted"),
     * ),
     * @OA\Schema(
     *   schema="RestoreWaiterResponse",
     *   description="Restore waiter response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Waiter"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     */
}
