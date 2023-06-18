<?php

namespace App\Http\Resources\Restaurant;

use App\Http\Resources\Media\MediaCollection;
use App\Http\Resources\Schedule\ScheduleCollection;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

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
            'timezone' => $this->timezone,
            'timezone_offset' => $this->timezone_offset,
            'popularity' => $this->popularity,
            'media' => new MediaCollection($this->media),
            'schedules' => new ScheduleCollection($this->whenLoaded('schedules')),
        ];
    }

    /**
     * @OA\Schema(
     *   schema="Restaurant",
     *   description="Restaurant resource object",
     *   required = {"id", "type", "slug", "name", "country", "city", "place",
     *      "timezone", "timezone_offset", "popularity", "media"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="type", type="string", example="restaurants"),
     *   @OA\Property(property="slug", type="string", example="first"),
     *   @OA\Property(property="name", type="string", example="First"),
     *   @OA\Property(property="country", type="string", example="Ukraine"),
     *   @OA\Property(property="city", type="string", example="Uzhhorod"),
     *   @OA\Property(property="place", type="string", example="Koryatovycha Square, 1а"),
     *   @OA\Property(property="timezone", type="string", example="Europe/Kyiv"),
     *   @OA\Property(property="timezone_offset", type="integer", example=180,
     *     description="Selected timezone offset in minutes."),
     *   @OA\Property(property="popularity", type="integer", nullable="true", example=1),
     *   @OA\Property(property="media", type="array", @OA\Items(ref ="#/components/schemas/Media")),
     *   @OA\Property(property="schedules", type="array", @OA\Items(ref ="#/components/schemas/Schedule")),
     * )
     */
}
