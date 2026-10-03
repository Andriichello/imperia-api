<?php

namespace App\Http\Resources\Editor;

use App\Helpers\ContentLocale;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * Class EditorDashboardResource.
 *
 * What the admin's dashboard shows of a restaurant. Whether it's open now is worked out in
 * the browser from the hours, like the public page does it.
 *
 * @mixin Restaurant
 */
class EditorDashboardResource extends EditorRestaurantResource
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
        $timezone = $this->timezone ?: config('app.timezone');

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'url' => $this->publicUrl(),
            'default_locale' => $this->getDefaultLocale(),
            'supported_locales' => ContentLocale::supported(),
            'name' => $this->translations($this->resource, 'name'),
            'establishment' => $this->establishment,
            'timezone' => $timezone,
            'currency' => $this->currency,
            'menus_count' => (int) $this->getAttribute('menus_count'),
            'dishes_count' => (int) $this->getAttribute('dishes_count'),
            'last_saved_at' => $this->last_saved_at?->clone()->setTimezone($timezone)->toIso8601String(),
            'last_saved_by' => $this->lastSavedBy
                ? ['id' => $this->lastSavedBy->id, 'name' => $this->lastSavedBy->name]
                : null,
            'closed_until' => $this->closed_until?->format('Y-m-d'),
            'closed_reason' => $this->translations($this->resource, 'closed_reason'),
            'weekdays' => $this->weekdays(),
            'exceptions' => EditorScheduleExceptionResource::collection($this->scheduleExceptions),
            'versions' => EditorVersionResource::collection($this->versions),
            'reviews' => $this->getAttribute('reviews_summary'),
            'pending_reviews' => (int) $this->getAttribute('pending_reviews'),
        ];
    }

    /**
     * @OA\Schema(
     *   schema="EditorDashboard",
     *   description="What the dashboard shows: what guests see, hours, upcoming special days, pending versions.",
     *   required={"id", "slug", "url", "default_locale", "supported_locales", "name", "establishment", "timezone",
     *     "currency", "menus_count", "dishes_count", "last_saved_at", "last_saved_by", "closed_until",
     *     "closed_reason", "weekdays", "exceptions", "versions", "reviews", "pending_reviews"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="slug", type="string", nullable=true, example="smak"),
     *   @OA\Property(property="url", type="string", example="https://example.com/en/web/smak"),
     *   @OA\Property(property="default_locale", type="string", example="en"),
     *   @OA\Property(property="supported_locales", type="array", @OA\Items(type="string"), example={"en", "uk"}),
     *   @OA\Property(property="name", ref="#/components/schemas/EditorTranslations"),
     *   @OA\Property(property="establishment", type="string", nullable=true, example="restaurant"),
     *   @OA\Property(property="timezone", type="string", example="Europe/Kyiv"),
     *   @OA\Property(property="currency", type="string", nullable=true, example="UAH"),
     *   @OA\Property(property="menus_count", type="integer", example=3, description="Menus guests see."),
     *   @OA\Property(property="dishes_count", type="integer", example=42, description="Dishes guests see."),
     *   @OA\Property(property="last_saved_at", type="string", format="date-time", nullable=true),
     *   @OA\Property(property="last_saved_by", type="object", nullable=true, required={"id", "name"},
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="name", type="string", example="Anna")),
     *   @OA\Property(property="closed_until", type="string", format="date", nullable=true),
     *   @OA\Property(property="closed_reason", ref="#/components/schemas/EditorTranslations"),
     *   @OA\Property(property="weekdays", ref="#/components/schemas/EditorWeekdays"),
     *   @OA\Property(property="exceptions", type="array",
     *     @OA\Items(ref="#/components/schemas/EditorScheduleException"),
     *     description="Special days, which haven't ended yet, from the nearest."),
     *   @OA\Property(property="versions", type="array", @OA\Items(ref="#/components/schemas/EditorVersion")),
     *   @OA\Property(property="reviews", ref="#/components/schemas/RestaurantReviewsSummary"),
     *   @OA\Property(property="pending_reviews", type="integer", example=3,
     *     description="Reviews, which wait for approval."),
     * ),
     */
}
