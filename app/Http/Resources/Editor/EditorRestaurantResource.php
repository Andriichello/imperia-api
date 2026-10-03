<?php

namespace App\Http\Resources\Editor;

use App\Enums\Weekday;
use App\Helpers\ContentLocale;
use App\Http\Resources\Media\MediaCollection;
use App\Models\Restaurant;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class EditorRestaurantResource.
 *
 * Everything of a restaurant the editor shows, in all languages.
 *
 * @mixin Restaurant
 */
class EditorRestaurantResource extends JsonResource
{
    use FormatsTranslations;

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
        $default = $this->getDefaultLocale();

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'url' => $this->publicUrl(),
            'default_locale' => $default,
            'supported_locales' => ContentLocale::supported(),
            'name' => $this->translations($this->resource, 'name'),
            'establishment' => $this->establishment,
            'phone' => $this->phone,
            'address' => $this->translations($this->resource, 'address'),
            'timezone' => $this->timezone,
            'currency' => $this->currency,
            'brand_primary' => $this->brand_primary,
            'brand_primary_content' => $this->brand_primary_content,
            'closed_until' => $this->closed_until?->format('Y-m-d'),
            'closed_reason' => $this->translations($this->resource, 'closed_reason'),
            'notes' => EditorNoteResource::collection($this->whenLoaded('notes')),
            'photos' => new MediaCollection($this->whenLoaded('allMedia')),
            'weekdays' => $this->when($this->relationLoaded('schedules'), fn () => $this->weekdays()),
            'exceptions' => EditorScheduleExceptionResource::collection($this->whenLoaded('scheduleExceptions')),
            'menus' => EditorMenuResource::collection($this->whenLoaded('dishMenus')),
            'versions' => EditorVersionResource::collection($this->whenLoaded('versions')),
        ];
    }

    /**
     * Address of the restaurant's public page, in its default language.
     *
     * @return string
     */
    protected function publicUrl(): string
    {
        return route('web.restaurant.preview', [
            'locale' => $this->getDefaultLocale(),
            'restaurant_id' => $this->slug ?: $this->id,
        ]);
    }

    /**
     * Opening intervals by weekday, from the earliest one (closed days have none).
     *
     * @return array<string, array>
     */
    protected function weekdays(): array
    {
        $weekdays = array_fill_keys(Weekday::getValues(), []);

        $this->schedules
            ->reject(fn (Schedule $schedule) => (bool) $schedule->archived)
            ->sortBy(fn (Schedule $schedule) => $schedule->beg_hour * 60 + $schedule->beg_minute)
            ->each(function (Schedule $schedule) use (&$weekdays) {
                $weekdays[$schedule->weekday][] = [
                    'id' => $schedule->id,
                    'beg_hour' => $schedule->beg_hour,
                    'beg_minute' => $schedule->beg_minute,
                    'end_hour' => $schedule->end_hour,
                    'end_minute' => $schedule->end_minute,
                ];
            });

        return $weekdays;
    }

    /**
     * @OA\Schema(
     *   schema="EditorTranslations",
     *   description="Translations of a text by language, `null` where there's none.",
     *   type="object",
     *   @OA\Property(property="en", type="string", nullable=true, example="Soups"),
     *   @OA\Property(property="uk", type="string", nullable=true, example="Супи"),
     * ),
     * @OA\Schema(
     *   schema="EditorInterval",
     *   description="Opening interval of a day. Closing earlier than opening is after midnight.",
     *   required={"beg_hour", "beg_minute", "end_hour", "end_minute"},
     *   @OA\Property(property="id", type="integer", example=1, description="Only in responses."),
     *   @OA\Property(property="beg_hour", type="integer", example=10),
     *   @OA\Property(property="beg_minute", type="integer", example=0),
     *   @OA\Property(property="end_hour", type="integer", example=22),
     *   @OA\Property(property="end_minute", type="integer", example=0),
     * ),
     * @OA\Schema(
     *   schema="EditorWeekdays",
     *   description="Opening intervals by weekday, closed days have none.",
     *   type="object",
     *   @OA\Property(property="monday", type="array", @OA\Items(ref="#/components/schemas/EditorInterval")),
     *   @OA\Property(property="tuesday", type="array", @OA\Items(ref="#/components/schemas/EditorInterval")),
     *   @OA\Property(property="wednesday", type="array", @OA\Items(ref="#/components/schemas/EditorInterval")),
     *   @OA\Property(property="thursday", type="array", @OA\Items(ref="#/components/schemas/EditorInterval")),
     *   @OA\Property(property="friday", type="array", @OA\Items(ref="#/components/schemas/EditorInterval")),
     *   @OA\Property(property="saturday", type="array", @OA\Items(ref="#/components/schemas/EditorInterval")),
     *   @OA\Property(property="sunday", type="array", @OA\Items(ref="#/components/schemas/EditorInterval")),
     * ),
     * @OA\Schema(
     *   schema="EditorRestaurantItem",
     *   description="Restaurant the user can edit.",
     *   required={"id", "slug", "name", "default_locale", "photo"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="slug", type="string", example="smak"),
     *   @OA\Property(property="name", type="string", example="Smak",
     *     description="In the current language."),
     *   @OA\Property(property="default_locale", type="string", example="en"),
     *   @OA\Property(property="photo", type="string", nullable=true,
     *     example="https://example.com/storage/media/cover.jpg", description="Its cover."),
     * ),
     * @OA\Schema(
     *   schema="EditorRestaurant",
     *   description="Everything of a restaurant the editor shows, in all languages.",
     *   required={"id", "slug", "url", "default_locale", "supported_locales", "name", "establishment",
     *     "phone", "address", "timezone", "currency", "brand_primary", "brand_primary_content",
     *     "closed_until", "closed_reason", "notes", "photos", "weekdays", "exceptions", "menus"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="slug", type="string", example="smak"),
     *   @OA\Property(property="url", type="string", example="https://example.com/en/web/smak",
     *     description="Public page of the restaurant."),
     *   @OA\Property(property="default_locale", type="string", example="en",
     *     description="Language, which required texts have to be written in."),
     *   @OA\Property(property="supported_locales", type="array", @OA\Items(type="string"),
     *     example={"en", "uk"}),
     *   @OA\Property(property="name", ref="#/components/schemas/EditorTranslations"),
     *   @OA\Property(property="establishment", type="string", nullable=true, example="restaurant",
     *     enum={"restaurant", "cafe", "bakery", "bistro", "pizzeria", "bar"}),
     *   @OA\Property(property="phone", type="string", nullable=true, example="+380441234567"),
     *   @OA\Property(property="address", ref="#/components/schemas/EditorTranslations"),
     *   @OA\Property(property="timezone", type="string", example="Europe/Kyiv"),
     *   @OA\Property(property="currency", type="string", nullable=true, example="UAH"),
     *   @OA\Property(property="brand_primary", type="string", nullable=true, example="#3bb517"),
     *   @OA\Property(property="brand_primary_content", type="string", nullable=true, example="#284625"),
     *   @OA\Property(property="closed_until", type="string", format="date", nullable=true,
     *     example="2026-10-15", description="Temporarily closed till this day."),
     *   @OA\Property(property="closed_reason", ref="#/components/schemas/EditorTranslations"),
     *   @OA\Property(property="notes", type="array", @OA\Items(ref="#/components/schemas/EditorNote")),
     *   @OA\Property(property="photos", type="array", @OA\Items(ref="#/components/schemas/Media")),
     *   @OA\Property(property="weekdays", ref="#/components/schemas/EditorWeekdays"),
     *   @OA\Property(property="exceptions", type="array",
     *     @OA\Items(ref="#/components/schemas/EditorScheduleException")),
     *   @OA\Property(property="menus", type="array", @OA\Items(ref="#/components/schemas/EditorMenu")),
     *   @OA\Property(property="versions", type="array", @OA\Items(ref="#/components/schemas/EditorVersion"),
     *     description="Versions, which haven't gone live yet, with their changes."),
     * ),
     */
}
