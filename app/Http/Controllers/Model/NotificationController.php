<?php

namespace App\Http\Controllers\Model;

use App\Http\Controllers\CrudController;
use App\Http\Requests\Notification\IndexNotificationRequest;
use App\Http\Requests\Notification\ShowNotificationRequest;
use App\Http\Resources\Notification\NotificationCollection;
use App\Http\Resources\Notification\NotificationResource;
use App\Repositories\NotificationRepository;

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
     *   summary="Show notifications by id.",
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
     */
}
