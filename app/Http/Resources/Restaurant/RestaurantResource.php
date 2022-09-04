<?php

namespace App\Http\Resources\Restaurant;

use App\Http\Resources\Holiday\HolidayCollection;
use App\Http\Resources\Media\MediaCollection;
use App\Http\Resources\Schedule\ScheduleCollection;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class RestaurantResource.
 *
 * @mixin Restaurant
 */
class RestaurantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'slug' => $this->slug,
            'name' => $this->name,
            'country' => $this->country,
            'city' => $this->city,
            'place' => $this->place,
            'media' => new MediaCollection($this->media),
            'schedules' => new ScheduleCollection($this->operativeSchedules),
            'holidays' => new HolidayCollection($this->closestHolidays),
        ];
    }

    /**
     * @OA\Schema(
     *   schema="Restaurant",
     *   description="Restaurant resource object",
     *   required = {"id", "type", "slug", "name", "country", "city", "place", "media", "schedules"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="type", type="string", example="customers"),
     *   @OA\Property(property="slug", type="string", example="first"),
     *   @OA\Property(property="name", type="string", example="First"),
     *   @OA\Property(property="country", type="string", example="Ukraine"),
     *   @OA\Property(property="city", type="string", example="Uzhhorod"),
     *   @OA\Property(property="place", type="string", example="Koryatovycha Square, 1а"),
     *   @OA\Property(property="media", type="array", @OA\Items(ref ="#/components/schemas/Media")),
     *   @OA\Property(property="schedules", type="array", @OA\Items(ref ="#/components/schemas/Schedule"),
     *     description="Restaurant's operative schedules."),
     *   @OA\Property(property="holidays", type="array", @OA\Items(ref ="#/components/schemas/Holiday"),
     *     description="Restaurant's closest holidays (for 7 days)."),
     * )
     */
}
