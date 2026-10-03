<?php

namespace App\Http\Resources\Restaurant;

use App\Models\RestaurantReview;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class RestaurantReviewResource.
 *
 * A guest's review on the public site: neither the guest's IP nor their device is in it.
 *
 * @mixin RestaurantReview
 */
class RestaurantReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'name' => $this->name,
            'text' => $this->text,
            'locale' => $this->locale,
            'status' => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    /**
     * @OA\Schema(
     *   schema="RestaurantReview",
     *   description="A guest's review of a restaurant, in the language it was written in.",
     *   required={"id", "rating", "name", "text", "locale", "status", "created_at"},
     *   @OA\Property(property="id", type="integer", nullable=true, example=1,
     *     description="None, when the review wasn't kept."),
     *   @OA\Property(property="rating", type="integer", minimum=1, maximum=5, example=5),
     *   @OA\Property(property="name", type="string", nullable=true, example="Olena",
     *     description="None for a guest, who didn't give it."),
     *   @OA\Property(property="text", type="string", nullable=true,
     *     example="Best borscht I've had in Kyiv.", description="None for a rating only."),
     *   @OA\Property(property="locale", type="string", nullable=true, example="en"),
     *   @OA\Property(property="status", type="string", example="approved",
     *     enum={"pending", "approved", "rejected"},
     *     description="Public reviews are approved; a guest sees the status of their own ones."),
     *   @OA\Property(property="created_at", type="string", format="date-time", nullable=true,
     *     example="2026-10-03T12:00:00+00:00"),
     * ),
     * @OA\Schema(
     *   schema="RestaurantReviewsSummary",
     *   description="The approved reviews of a restaurant: their average, count and counts by rating (5 to 1).",
     *   required={"average", "count", "ratings"},
     *   @OA\Property(property="average", type="number", nullable=true, example=4.6,
     *     description="To one decimal; none without reviews."),
     *   @OA\Property(property="count", type="integer", example=128),
     *   @OA\Property(property="ratings", type="array", @OA\Items(type="object", required={"rating", "count"},
     *     @OA\Property(property="rating", type="integer", example=5),
     *     @OA\Property(property="count", type="integer", example=96))),
     * ),
     */
}
