<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\RestaurantReview\IndexRestaurantReviewsRequest;
use App\Http\Requests\RestaurantReview\MyRestaurantReviewsRequest;
use App\Http\Requests\RestaurantReview\StoreRestaurantReviewRequest;
use App\Http\Resources\Restaurant\RestaurantReviewResource;
use App\Http\Responses\ApiResponse;
use App\Models\RestaurantReview;
use App\Repositories\RestaurantReviewRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use OpenApi\Annotations as OA;

/**
 * Class ReviewController.
 *
 * Reviews of a restaurant on the public site. Guests leave them, and they're public once the
 * restaurant approves them. A device leaves one review of a restaurant a day; besides that, an IP
 * leaves a few an hour (`throttle:reviews`).
 */
class ReviewController extends Controller
{
    /**
     * ReviewController constructor.
     *
     * @param RestaurantReviewRepository $repository
     */
    public function __construct(protected RestaurantReviewRepository $repository)
    {
    }

    /**
     * A page of the approved reviews, with their summary.
     *
     * @param IndexRestaurantReviewsRequest $request
     *
     * @return ApiResponse
     */
    public function index(IndexRestaurantReviewsRequest $request): ApiResponse
    {
        $restaurant = $request->restaurant();
        $page = $this->repository->approved(
            $restaurant,
            $request->validated('sort', 'newest'),
            (int) $request->validated('page', 1),
        );

        return ApiResponse::make([
            'data' => RestaurantReviewResource::collection($page->items()),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
            ],
            'summary' => $this->repository->summary($restaurant),
        ]);
    }

    /**
     * Leave a review: it waits for the restaurant to approve it.
     *
     * @param StoreRestaurantReviewRequest $request
     *
     * @return ApiResponse
     */
    public function store(StoreRestaurantReviewRequest $request): ApiResponse
    {
        $restaurant = $request->restaurant();
        $data = $request->validated();

        // a bot filled in the field guests don't see: the review looks left, but isn't kept
        if (filled($data['website'] ?? null)) {
            $review = new RestaurantReview([
                ...Arr::only($data, ['rating', 'name', 'text', 'locale']),
                'restaurant_id' => $restaurant->id,
            ]);
            $review->created_at = Carbon::now();

            return ApiResponse::make(['data' => new RestaurantReviewResource($review)], 201, 'Created');
        }

        if ($this->repository->postedLately($restaurant, $data['client_token'])) {
            return ApiResponse::make([], 429, 'A review of the restaurant was left from this device today.');
        }

        $review = $this->repository->create($restaurant, $data, (string) $request->ip(), $data['client_token']);

        return ApiResponse::make(['data' => new RestaurantReviewResource($review)], 201, 'Created');
    }

    /**
     * The device's own reviews, whatever their status: it shows the ones, which wait for approval.
     *
     * @param MyRestaurantReviewsRequest $request
     *
     * @return ApiResponse
     */
    public function mine(MyRestaurantReviewsRequest $request): ApiResponse
    {
        $reviews = $this->repository->mine(
            $request->restaurant(),
            $request->validated('ids'),
            $request->validated('client_token'),
        );

        return ApiResponse::make(['data' => RestaurantReviewResource::collection($reviews)]);
    }

    /**
     * @OA\Get(
     *   path="/api/restaurants/{id}/reviews",
     *   summary="A page of the restaurant's approved reviews, with their summary.",
     *   operationId="getRestaurantReviews",
     *   tags={"restaurant-reviews"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="string"),
     *     description="Id or slug of the restaurant."),
     *   @OA\Parameter(name="sort", required=false, in="query", example="newest",
     *     @OA\Schema(type="string", enum={"newest", "highest", "lowest"})),
     *   @OA\Parameter(name="page", required=false, in="query", example=1, @OA\Schema(type="integer")),
     *   @OA\Response(
     *     response=200,
     *     description="Success.",
     *     @OA\JsonContent(ref="#/components/schemas/RestaurantReviewsResponse")
     *   ),
     *   @OA\Response(response=404, description="The restaurant doesn't exist."),
     * ),
     * @OA\Post(
     *   path="/api/restaurants/{id}/reviews",
     *   summary="Leave a review of the restaurant: it waits for the restaurant to approve it.",
     *   operationId="storeRestaurantReview",
     *   tags={"restaurant-reviews"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="string"),
     *     description="Id or slug of the restaurant."),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/StoreRestaurantReviewRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Created.",
     *     @OA\JsonContent(ref="#/components/schemas/RestaurantReviewResponse")
     *   ),
     *   @OA\Response(response=404, description="The restaurant doesn't exist."),
     *   @OA\Response(response=422, description="Invalid values.",
     *     @OA\JsonContent(ref="#/components/schemas/ValidationErrorsResponse")),
     *   @OA\Response(response=429, description="A review was left from the device today, or too many from the IP."),
     * ),
     * @OA\Post(
     *   path="/api/restaurants/{id}/reviews/mine",
     *   summary="The device's own reviews of the restaurant, whatever their status.",
     *   operationId="getMyRestaurantReviews",
     *   tags={"restaurant-reviews"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="string"),
     *     description="Id or slug of the restaurant."),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/MyRestaurantReviewsRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Success.",
     *     @OA\JsonContent(ref="#/components/schemas/MyRestaurantReviewsResponse")
     *   ),
     *   @OA\Response(response=404, description="The restaurant doesn't exist."),
     *   @OA\Response(response=422, description="Invalid values.",
     *     @OA\JsonContent(ref="#/components/schemas/ValidationErrorsResponse")),
     * ),
     *
     * @OA\Schema(
     *   schema="RestaurantReviewsResponse",
     *   description="A page of the restaurant's approved reviews, with their summary.",
     *   required={"data", "meta", "summary", "message"},
     *   @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/RestaurantReview")),
     *   @OA\Property(property="meta", type="object", required={"current_page", "last_page", "per_page", "total"},
     *     @OA\Property(property="current_page", type="integer", example=1),
     *     @OA\Property(property="last_page", type="integer", example=13),
     *     @OA\Property(property="per_page", type="integer", example=10),
     *     @OA\Property(property="total", type="integer", example=128)),
     *   @OA\Property(property="summary", ref="#/components/schemas/RestaurantReviewsSummary"),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     * @OA\Schema(
     *   schema="RestaurantReviewResponse",
     *   description="The review, which was left.",
     *   required={"data", "message"},
     *   @OA\Property(property="data", ref="#/components/schemas/RestaurantReview"),
     *   @OA\Property(property="message", type="string", example="Created"),
     * ),
     * @OA\Schema(
     *   schema="MyRestaurantReviewsResponse",
     *   description="The device's own reviews of the restaurant.",
     *   required={"data", "message"},
     *   @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/RestaurantReview")),
     *   @OA\Property(property="message", type="string", example="Success"),
     * ),
     */
}
