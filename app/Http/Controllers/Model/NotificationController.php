<?php

namespace App\Http\Controllers\Model;

use App\Enums\NotificationChannel;
use App\Http\Controllers\CrudController;
use App\Http\Requests\CrudRequest;
use App\Http\Requests\Notification\DestroyNotificationRequest;
use App\Http\Requests\Notification\IndexNotificationRequest;
use App\Http\Requests\Notification\SeeNotificationRequest;
use App\Http\Requests\Notification\ShowNotificationRequest;
use App\Http\Requests\Notification\StoreNotificationRequest;
use App\Http\Requests\Notification\UpdateNotificationRequest;
use App\Http\Resources\Notification\NotificationCollection;
use App\Http\Resources\Notification\NotificationResource;
use App\Http\Responses\ApiResponse;
use App\Queries\NotificationQueryBuilder;
use App\Repositories\NotificationRepository;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

/**
 * Class NotificationController.
 */
class NotificationController extends CrudController
{
    /**
     * Controller's model resource class.
     *
     * @var string
     */
    protected string $resourceClass = NotificationResource::class;

    /**
     * Controller's model resource collection class.
     *
     * @var string
     */
    protected string $collectionClass = NotificationCollection::class;

    /**
     * NotificationController constructor.
     *
     * @param NotificationRepository $repository
     */
    public function __construct(NotificationRepository $repository)
    {
        parent::__construct($repository);
        $this->actions['index'] = IndexNotificationRequest::class;
        $this->actions['show'] = ShowNotificationRequest::class;
        $this->actions['store'] = StoreNotificationRequest::class;
        $this->actions['update'] = UpdateNotificationRequest::class;
        $this->actions['destroy'] = DestroyNotificationRequest::class;
    }

    /**
     * @param CrudRequest $request
     *
     * @return EloquentBuilder
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function builder(CrudRequest $request): EloquentBuilder
    {
        /** @var NotificationQueryBuilder $builder */
        $builder = parent::builder($request);

        $channels = [NotificationChannel::Default];
        return $builder->inChannels(...$channels)
            ->forUser($request->user());
    }

    /**
     * Show notification by id. If user, who makes a request is a
     * receiver of the notification then it is marked as seen.
     *
     * @param ShowNotificationRequest $request
     *
     * @return ApiResponse
     */
    protected function show(ShowNotificationRequest $request): ApiResponse
    {
        $notification = $request->getNotification();

        if ($request->isByReceiver()) {
            $notification->seen_at = now();
            $notification->save();
        }

        return $this->asResourceResponse($notification);
    }

    /**
     * @OA\Get(
     *   path="/api/notifications",
     *   summary="Index notifications.",
     *   operationId="indexNotifications",
     *   security={{"bearerAuth": {}}},
     *   tags={"notifications"},
     *
     *   @OA\Parameter(name="page[size]", in="query", @OA\Schema(ref ="#/components/schemas/PageSize")),
     *   @OA\Parameter(name="page[number]", in="query", @OA\Schema(ref ="#/components/schemas/PageNumber")),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Index notifications response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/IndexNotificationResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Get(
     *   path="/api/notifications/{id}",
     *   summary="Show notification by id.",
     *   operationId="showNotification",
     *   security={{"bearerAuth": {}}},
     *   tags={"notifications"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the notification."),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Show notification response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/ShowNotificationResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Post(
     *   path="/api/notifications",
     *   summary="Store notification.",
     *   operationId="storeNotification",
     *   security={{"bearerAuth": {}}},
     *   tags={"notifications"},
     *
     *  @OA\RequestBody(
     *     required=true,
     *     description="Store notification request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/StoreNotificationRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Create notification response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/StoreNotificationResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Patch(
     *   path="/api/notifications/{id}",
     *   summary="Update notification.",
     *   operationId="updateNotification",
     *   security={{"bearerAuth": {}}},
     *   tags={"notifications"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the notification."),
     *  @OA\RequestBody(
     *     required=true,
     *     description="Update notification request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/UpdateNotificationRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Update notification response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/UpdateNotificationResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Delete(
     *   path="/api/notifications/{id}",
     *   summary="Delete notification.",
     *   operationId="destroyNotification",
     *   security={{"bearerAuth": {}}},
     *   tags={"notifications"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the notification."),
     *
     *  @OA\RequestBody(
     *     required=false,
     *     description="Delete request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/DestroyRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Delete notification response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/DestroyNotificationResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     *
     *
     * @OA\Schema(
     *   schema="IndexNotificationResponse",
     *   description="Index notifications response object.",
     *   required = {"data", "meta", "message"},
     *   @OA\Property(property="data", type="array", @OA\Items(ref ="#/components/schemas/Notification")),
     *   @OA\Property(property="meta", ref ="#/components/schemas/PaginationMeta"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="ShowNotificationResponse",
     *   description="Show notification response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Notification"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="StoreNotificationResponse",
     *   description="Store notification response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Notification"),
     *   @OA\Property(property="message", type="string", example="Created"),
     * ),
     * @OA\Schema(
     *   schema="UpdateNotificationResponse",
     *   description="Update notification response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Notification"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="DestroyNotificationResponse",
     *   description="Delete notification response object.",
     *   required = {"message"},
     *   @OA\Property(property="message", type="string", example="Deleted"),
     * ),
     * @OA\Schema(
     *   schema="RestoreNotificationResponse",
     *   description="Restore notification response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Notification"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     */
}
