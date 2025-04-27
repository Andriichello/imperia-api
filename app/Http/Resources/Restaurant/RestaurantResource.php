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
            'full_address' => $this->full_address,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            'location' => $this->location,
            'timezone' => $this->timezone,
            'timezone_offset' => $this->timezone_offset,
            'popularity' => $this->popularity,
            'notes' => $this->notes,
            /* @phpstan-ignore-next-line */
            'media' => new MediaCollection($this->media->load('variants')),
            'schedules' => new ScheduleCollection($this->schedules),
        ];
    }

    /**
     * @OA\Schema(
     *   schema="Restaurant",
     *   description="Restaurant resource object",
     *   required = {"id", "type", "slug", "name", "country", "city", "place",
     *     "phone", "email", "website", "location", "timezone", "timezone_offset",
     *     "popularity", "notes", "media", "schedules"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="type", type="string", example="restaurants"),
     *   @OA\Property(property="slug", type="string", example="first"),
     *   @OA\Property(property="name", type="string", example="First"),
     *   @OA\Property(property="country", type="string", example="Ukraine"),
     *   @OA\Property(property="city", type="string", example="Uzhhorod"),
     *   @OA\Property(property="place", type="string", example="Koryatovycha Square, 1а"),
     *   @OA\Property(property="full_address", type="string",
     *     example="Koryatovycha Square, 1а, Uzhhorod, Ukraine"),
     *   @OA\Property(property="phone", type="string", nullable="true",
     *     example="+380501234567"),
     *   @OA\Property(property="email", type="string", nullable="true",
     *     example="imperia@email.com"),
     *   @OA\Property(property="location", type="string", nullable="true",
     *      example="https://goo.gl/maps/g7XZq9H712osMLZW9"),
     *   @OA\Property(property="website", type="string", nullable="true",
     *     example="https://app.imperia.pp.ua"),
     *   @OA\Property(property="timezone", type="string", example="Europe/Kyiv"),
     *   @OA\Property(property="timezone_offset", type="integer", example=180,
     *     description="Selected timezone offset in minutes."),
     *   @OA\Property(property="popularity", type="integer", nullable="true", example=1),
     *   @OA\Property(property="notes", type="array", nullable="true", @OA\Items(type="string")),
     *   @OA\Property(property="media", type="array", @OA\Items(ref ="#/components/schemas/Media")),
     *   @OA\Property(property="schedules", type="array", @OA\Items(ref ="#/components/schemas/Schedule")),
     * )
     */
}
