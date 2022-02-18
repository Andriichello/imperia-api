<?php

namespace App\Http\Controllers\Model;

use App\Http\Controllers\CrudController;
use App\Http\Requests\Crud\ShowRequest;
use App\Http\Requests\Order\IndexOrderRequest;
use App\Http\Requests\Order\ShowOrderRequest;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Http\Resources\Order\OrderCollection;
use App\Http\Resources\Order\OrderResource;
use App\Http\Responses\ApiResponse;
use App\Models\Banquet;
use App\Repositories\OrderRepository;

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
     */
    public function __construct(OrderRepository $repository)
    {
        parent::__construct($repository);
        $this->actions['index'] = IndexOrderRequest::class;
        $this->actions['show'] = ShowOrderRequest::class;
        $this->actions['store'] = StoreOrderRequest::class;
        $this->actions['update'] = UpdateOrderRequest::class;
    }

    /**
     * Show order by banquet id.
     *
     * @param int $banquetId
     * @param ShowOrderRequest $request
     *
     * @return ApiResponse
     */
    protected function showByBanquetId(int $banquetId, ShowOrderRequest $request): ApiResponse
    {
        /** @var Banquet $model @phpstan-ignore-next-line */
        $model = $this->spatieBuilder($request)->withTrashed()
            ->where('banquet_id', $banquetId)
            ->firstOrFail();
        return $this->asResourceResponse($model);
    }

    /**
     * @OA\Get(
     *   path="/api/orders",
     *   summary="Index orders.",
     *   operationId="indexOrders",
     *   security={{"bearerAuth": {}}},
     *   tags={"orders"},
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
     *
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
     */
}
