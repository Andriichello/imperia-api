<?php

namespace App\Http\Resources\Restaurant;

use App\Models\RestaurantReview;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class RestaurantReviewResource.
 *
 * @mixin RestaurantReview
 */
class RestaurantReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'restaurant_id' => $this->restaurant_id,
            'ip' => $this->ip,
            'reviewer' => $this->reviewer,
            'score' => $this->score,
            'title' => $this->title,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * @OA\Schema(
     *   schema="RestaurantReview",
     *   description="Restaurant review resource object",
     *   required = {"id", "type", "restaurant_id", "ip", "reviewer", "score",
     *     "title", "description", "created_at", "updated_at"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="type", type="string", example="restaurant-reviews"),
     *   @OA\Property(property="restaurant_id", type="integer", example=1),
     *   @OA\Property(property="ip", type="string", example="127.0.0.1"),
     *   @OA\Property(property="reviewer", type="string", example="Steve"),
     *   @OA\Property(property="score", type="integer", example=5,
     *     description="Min value: `0`, max value: `5`."),
     *   @OA\Property(property="title", type="string", nullable=true, example="Great"),
     *   @OA\Property(property="description", type="string", nullable=true,
     *     example="The restaurant is great and the personnel is nice."),
     *   @OA\Property(property="created_at", type="string", format="date-time",
     *     nullable=true, example="2022-01-12 10:00:00"),
     *   @OA\Property(property="updated_at", type="string", format="date-time",
     *     nullable=true, example="2022-01-12 10:00:00"),
     * )
     */
}
