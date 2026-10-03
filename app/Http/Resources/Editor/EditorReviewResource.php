<?php

namespace App\Http\Resources\Editor;

use App\Models\RestaurantReview;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class EditorReviewResource.
 *
 * A guest's review for its moderation: who approved or rejected it, and when.
 *
 * @mixin RestaurantReview
 */
class EditorReviewResource extends JsonResource
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
            'restaurant_id' => $this->restaurant_id,
            'rating' => $this->rating,
            'name' => $this->name,
            'text' => $this->text,
            'locale' => $this->locale,
            'status' => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
            'moderated_at' => $this->moderated_at?->toIso8601String(),
            'moderated_by' => $this->moderator
                ? ['id' => $this->moderator->id, 'name' => $this->moderator->name]
                : null,
        ];
    }

    /**
     * @OA\Schema(
     *   schema="EditorReview",
     *   description="A guest's review of the restaurant, for its moderation.",
     *   required={"id", "restaurant_id", "rating", "name", "text", "locale", "status", "created_at",
     *     "moderated_at", "moderated_by"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="restaurant_id", type="integer", example=1),
     *   @OA\Property(property="rating", type="integer", minimum=1, maximum=5, example=5),
     *   @OA\Property(property="name", type="string", nullable=true, example="Olena"),
     *   @OA\Property(property="text", type="string", nullable=true, example="Best borscht I've had in Kyiv."),
     *   @OA\Property(property="locale", type="string", nullable=true, example="en"),
     *   @OA\Property(property="status", type="string", example="pending", enum={"pending", "approved", "rejected"}),
     *   @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
     *   @OA\Property(property="moderated_at", type="string", format="date-time", nullable=true),
     *   @OA\Property(property="moderated_by", type="object", nullable=true, required={"id", "name"},
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="name", type="string", example="Anna")),
     * ),
     */
}
