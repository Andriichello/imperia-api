<?php

namespace App\Http\Controllers\Model;

use App\Http\Controllers\CrudController;
use App\Http\Requests\Comment\IndexCommentRequest;
use App\Http\Requests\Comment\ShowCommentRequest;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Http\Resources\Comment\CommentCollection;
use App\Http\Resources\Comment\CommentResource;
use App\Repositories\CommentRepository;

/**
 * Class CommentController.
 */
class CommentController extends CrudController
{
    /**
     * Controller's model resource class.
     *
     * @var string
     */
    protected string $resourceClass = CommentResource::class;

    /**
     * Controller's model resource collection class.
     *
     * @var string
     */
    protected string $collectionClass = CommentCollection::class;

    /**
     * CommentRepository constructor.
     *
     * @param CommentRepository $repository
     */
    public function __construct(CommentRepository $repository)
    {
        parent::__construct($repository);
        $this->actions['index'] = IndexCommentRequest::class;
        $this->actions['show'] = ShowCommentRequest::class;
        $this->actions['store'] = StoreCommentRequest::class;
        $this->actions['update'] = UpdateCommentRequest::class;
    }

    /**
     * @OA\Get(
     *   path="/api/comments",
     *   summary="Index comments.",
     *   operationId="indexComments",
     *   security={{"bearerAuth": {}}},
     *   tags={"comments"},
     *
     *  @OA\Parameter(name="filter[commentable_id]", required=true, in="query", example=1, @OA\Schema(type="integer"),
     *     description="Comment target morph lass id."),
     *  @OA\Parameter(name="filter[commentable_type]", required=true, in="query", example="customers",
     *      @OA\Schema(type="string"), description="Comment target class slug. Examples: `customers`, `banquets`,
    `orders`, `product-field-orders`, `service-field-orders`, `space-field-orders`, `ticket-field-orders`"),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Index comments response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/IndexCommentResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Get(
     *   path="/api/comments/{id}",
     *   summary="Show comment by id.",
     *   operationId="showComment",
     *   security={{"bearerAuth": {}}},
     *   tags={"comments"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the comment."),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Show commet response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/ShowCommentResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Post(
     *   path="/api/comments",
     *   summary="Store comment.",
     *   operationId="storeComment",
     *   security={{"bearerAuth": {}}},
     *   tags={"comments"},
     *
     *  @OA\RequestBody(
     *     required=true,
     *     description="Store comment request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/StoreCommentRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Create comment response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/StoreCommentResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Patch(
     *   path="/api/comments/{id}",
     *   summary="Update comment.",
     *   operationId="updateComment",
     *   security={{"bearerAuth": {}}},
     *   tags={"comments"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the comment."),
     *
     *  @OA\RequestBody(
     *     required=true,
     *     description="Update comment request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/UpdateCommentRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Update comment response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/UpdateCommentResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     * @OA\Delete(
     *   path="/api/comments/{id}",
     *   summary="Delete comment.",
     *   operationId="destroyComment",
     *   security={{"bearerAuth": {}}},
     *   tags={"comments"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the comment."),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Delete comment response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/DestroyCommentResponse")
     *   ),
     *   @OA\Response(
     *     response=401,
     *     description="Unauthenticated.",
     *     @OA\JsonContent(ref ="#/components/schemas/UnauthenticatedResponse")
     *   )
     * ),
     *
     * @OA\Schema(
     *   schema="IndexCommentResponse",
     *   description="Index comments response object.",
     *   required = {"data", "meta", "message"},
     *   @OA\Property(property="data", type="array", @OA\Items(ref ="#/components/schemas/Comment")),
     *   @OA\Property(property="meta", ref ="#/components/schemas/PaginationMeta"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="ShowCommentResponse",
     *   description="Show comment response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Comment"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="StoreCommentResponse",
     *   description="Store comment response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Comment"),
     *   @OA\Property(property="message", type="string", example="Created"),
     * ),
     * @OA\Schema(
     *   schema="UpdateCommentResponse",
     *   description="Update comment response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Comment"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="DestroyCommentResponse",
     *   description="Delete comment response object.",
     *   required = {"message"},
     *   @OA\Property(property="message", type="string", example="Deleted"),
     * ),
     */
}
