<?php

namespace App\Http\Controllers\Model;

use App\Http\Controllers\CrudController;
use App\Http\Requests\CrudRequest;
use App\Http\Requests\Order\IndexOrderRequest;
use App\Http\Requests\Order\ShowOrderRequest;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Http\Resources\Order\OrderCollection;
use App\Http\Resources\Order\OrderResource;
use App\Http\Responses\ApiResponse;
use App\Models\Banquet;
use App\Policies\OrderPolicy;
use App\Queries\OrderQueryBuilder;
use App\Repositories\OrderRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use OpenApi\Annotations as OA;

/**
 * Class OrderController.
 */
class OrderController extends CrudController
{
    /**
     * Controller's model resource class.
     *
     * @var string
     */
    protected string $resourceClass = OrderResource::class;

    /**
     * Controller's model resource collection class.
     *
     * @var string
     */
    protected string $collectionClass = OrderCollection::class;

    /**
     * OrderController constructor.
     *
     * @param OrderRepository $repository
     * @param OrderPolicy $policy
     */
    public function __construct(OrderRepository $repository, OrderPolicy $policy)
    {
        parent::__construct($repository, $policy);

        $this->actions['index'] = IndexOrderRequest::class;
        $this->actions['show'] = ShowOrderRequest::class;
        $this->actions['store'] = StoreOrderRequest::class;
        $this->actions['update'] = UpdateOrderRequest::class;
    }

    /**
     * Get eloquent query builder instance.
     *
     * @param CrudRequest $request
     *
     * @return OrderQueryBuilder
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function builder(CrudRequest $request): OrderQueryBuilder
    {
        /** @var OrderQueryBuilder $builder */
        $builder = parent::builder($request);

        return $builder->index($request->user());
    }

    /**
     * Show order by banquet id.
     *
     * @param ShowOrderRequest $request
     *
     * @return ApiResponse
     * @throws ModelNotFoundException
     */
    protected function showByBanquetId(ShowOrderRequest $request): ApiResponse
    {
        /** @var Banquet $banquet */
        $banquet = $request->targetOrFail(Banquet::class);

        if ($banquet->order_id) {
            $request->id($banquet->order_id);
            return $this->show($request);
        }

        throw new ModelNotFoundException("There is no order for selected banquet (id: $banquet->id)");
    }

    /**
     * @OA\Get(
     *   path="/api/orders",
     *   summary="Index orders.",
     *   operationId="indexOrders",
     *   security={{"bearerAuth": {}}},
     *   tags={"orders"},
     *
     *  @OA\Parameter(name="include", in="query",
     *     @OA\Schema(ref ="#/components/schemas/OrderIncludes")),
     *   @OA\Parameter(name="page[size]", in="query", @OA\Schema(ref ="#/components/schemas/PageSize")),
     *   @OA\Parameter(name="page[number]", in="query", @OA\Schema(ref ="#/components/schemas/PageNumber")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Index orders response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/IndexOrderResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Get(
     *   path="/api/orders/{id}",
     *   summary="Show orders by id.",
     *   operationId="showOrder",
     *   security={{"bearerAuth": {}}},
     *   tags={"orders"},
     *
     *  @OA\Parameter(name="include", in="query",
     *     @OA\Schema(ref ="#/components/schemas/OrderIncludes")),
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the order."),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Show order response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/ShowOrderResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Get(
     *   path="/api/banquets/{id}/order",
     *   summary="Show order by banquet id.",
     *   operationId="showOrderByBanquetId",
     *   security={{"bearerAuth": {}}},
     *   tags={"orders", "banquets"},
     *
     *  @OA\Parameter(name="include", in="query",
     *     @OA\Schema(ref ="#/components/schemas/OrderIncludes")),
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the banquet."),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Show order by banquet id response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/ShowOrderResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Post(
     *   path="/api/orders",
     *   summary="Store order.",
     *   operationId="storeOrder",
     *   security={{"bearerAuth": {}}},
     *   tags={"orders"},
     *
     *  @OA\RequestBody(
     *     required=true,
     *     description="Store order request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/StoreOrderRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Create order response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/StoreOrderResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Patch(
     *   path="/api/orders/{id}",
     *   summary="Update order.",
     *   operationId="updateOrder",
     *   security={{"bearerAuth": {}}},
     *   tags={"orders"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the order."),
     *
     *  @OA\RequestBody(
     *     required=true,
     *     description="Update order request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/UpdateOrderRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Update order response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/UpdateOrderResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Delete(
     *   path="/api/orders/{id}",
     *   summary="Delete order.",
     *   operationId="destroyOrder",
     *   security={{"bearerAuth": {}}},
     *   tags={"orders"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the order."),
     *
     *  @OA\RequestBody(
     *     required=false,
     *     description="Delete request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/DestroyRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Delete order response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/DestroyOrderResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Post(
     *   path="/api/orders/{id}/restore",
     *   summary="Restore order.",
     *   operationId="restoreOrder",
     *   security={{"bearerAuth": {}}},
     *   tags={"orders"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the order."),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Restore order response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/RestoreOrderResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     *
     * @OA\Schema(
     *   schema="OrderIncludes",
     *   description="Coma-separated list of inluded relations. Available relations:
    `comments`, `spaces.comments`, `tickets.comments`, `products.comments`, `services.comments`",
     *   type="string", example="comments,spaces.comments,tickets.comments,products.comments,services.comments"
     * ),
     * @OA\Schema(
     *   schema="IndexOrderResponse",
     *   description="Index orders response object.",
     *   required = {"data", "meta", "message"},
     *   @OA\Property(property="data", type="array", @OA\Items(ref ="#/components/schemas/Order")),
     *   @OA\Property(property="meta", ref ="#/components/schemas/PaginationMeta"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="ShowOrderResponse",
     *   description="Show order response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Order"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="StoreOrderResponse",
     *   description="Store order response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Order"),
     *   @OA\Property(property="message", type="string", example="Created"),
     * ),
     * @OA\Schema(
     *   schema="UpdateOrderResponse",
     *   description="Update order response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Order"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="DestroyOrderResponse",
     *   description="Delete order response object.",
     *   required = {"message"},
     *   @OA\Property(property="message", type="string", example="Deleted"),
     * ),
     * @OA\Schema(
     *   schema="RestoreOrderResponse",
     *   description="Restore order response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Order"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     */
}
