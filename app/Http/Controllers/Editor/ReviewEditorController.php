<?php

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Editor\IndexReviewsRequest;
use App\Http\Requests\Editor\ReviewActionRequest;
use App\Http\Resources\Editor\EditorReviewResource;
use App\Http\Responses\ApiResponse;
use App\Models\RestaurantReview;
use App\Repositories\RestaurantReviewRepository;
use OpenApi\Annotations as OA;

/**
 * Class ReviewEditorController.
 *
 * Moderation of the reviews guests leave: the restaurant approves them (they're public then) or
 * rejects them (spam, insults, personal details). Either can be decided again later.
 */
class ReviewEditorController extends Controller
{
    /**
     * ReviewEditorController constructor.
     *
     * @param RestaurantReviewRepository $repository
     */
    public function __construct(protected RestaurantReviewRepository $repository)
    {
    }

    /**
     * A page of the restaurant's reviews with the status, and how many have each status.
     *
     * @param IndexReviewsRequest $request
     *
     * @return ApiResponse
     */
    public function index(IndexReviewsRequest $request): ApiResponse
    {
        $restaurant = $request->restaurant();
        $page = $this->repository->withStatus(
            $restaurant,
            $request->validated('status', RestaurantReview::STATUS_PENDING),
            (int) $request->validated('page', 1),
        );

        return ApiResponse::make([
            'data' => EditorReviewResource::collection($page->items()),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
            ],
            'counts' => $this->repository->counts($restaurant),
        ]);
    }

    /**
     * Approve the review: it's public.
     *
     * @param ReviewActionRequest $request
     *
     * @return ApiResponse
     */
    public function approve(ReviewActionRequest $request): ApiResponse
    {
        return $this->moderate($request, RestaurantReview::STATUS_APPROVED);
    }

    /**
     * Reject the review: it's never public.
     *
     * @param ReviewActionRequest $request
     *
     * @return ApiResponse
     */
    public function reject(ReviewActionRequest $request): ApiResponse
    {
        return $this->moderate($request, RestaurantReview::STATUS_REJECTED);
    }

    /**
     * Give the review the status.
     *
     * @param ReviewActionRequest $request
     * @param string $status
     *
     * @return ApiResponse
     */
    protected function moderate(ReviewActionRequest $request, string $status): ApiResponse
    {
        /** @var RestaurantReview $review */
        $review = $request->target();
        $review = $this->repository->moderate($review, $status, $request->user());

        return ApiResponse::make(['data' => new EditorReviewResource($review)]);
    }

    /**
     * @OA\Get(
     *   path="/api/editor/restaurants/{id}/reviews",
     *   summary="A page of the restaurant's reviews with the status, and how many have each status.",
     *   operationId="getEditorReviews",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the restaurant."),
     *   @OA\Parameter(name="status", required=false, in="query", example="pending",
     *     @OA\Schema(type="string", enum={"pending", "approved", "rejected"}),
     *     description="The ones, which wait for approval, by default."),
     *   @OA\Parameter(name="page", required=false, in="query", example=1, @OA\Schema(type="integer")),
     *   @OA\Response(
     *     response=200,
     *     description="Success.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorReviewsResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     * ),
     * @OA\Post(
     *   path="/api/editor/reviews/{id}/approve",
     *   summary="Approve the review: it's public.",
     *   operationId="approveEditorReview",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the review."),
     *   @OA\Response(
     *     response=200,
     *     description="Success.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorReviewResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     * ),
     * @OA\Post(
     *   path="/api/editor/reviews/{id}/reject",
     *   summary="Reject the review: it's never public.",
     *   operationId="rejectEditorReview",
     *   security={{"bearerAuth": {}}},
     *   tags={"editor"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the review."),
     *   @OA\Response(
     *     response=200,
     *     description="Success.",
     *     @OA\JsonContent(ref="#/components/schemas/EditorReviewResponse")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated.",
     *     @OA\JsonContent(ref="#/components/schemas/UnauthenticatedResponse")),
     *   @OA\Response(response=403, description="The user can't edit the restaurant."),
     * ),
     *
     * @OA\Schema(
     *   schema="EditorReviewsResponse",
     *   description="A page of the restaurant's reviews with the status, and how many have each status.",
     *   required={"data", "meta", "counts", "message"},
     *   @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/EditorReview")),
     *   @OA\Property(property="meta", type="object", required={"current_page", "last_page", "per_page", "total"},
     *     @OA\Property(property="current_page", type="integer", example=1),
     *     @OA\Property(property="last_page", type="integer", example=1),
     *     @OA\Property(property="per_page", type="integer", example=20),
     *     @OA\Property(property="total", type="integer", example=3)),
     *   @OA\Property(property="counts", type="object", required={"pending", "approved", "rejected"},
     *     @OA\Property(property="pending", type="integer", example=3),
     *     @OA\Property(property="approved", type="integer", example=128),
     *     @OA\Property(property="rejected", type="integer", example=2)),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="EditorReviewResponse",
     *   description="The review with its new status.",
     *   required={"data", "message"},
     *   @OA\Property(property="data", ref="#/components/schemas/EditorReview"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     */
}
