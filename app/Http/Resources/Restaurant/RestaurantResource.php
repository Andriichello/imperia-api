<?php

namespace App\Http\Resources\Restaurant;

use App\Http\Resources\Media\MediaCollection;
use App\Http\Resources\Schedule\ScheduleCollection;
use App\Http\Resources\Schedule\ScheduleExceptionResource;
use App\Models\Restaurant;
use App\Models\RestaurantNote;
use App\Models\ScheduleException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
            'locale' => $this->locale,
            'currency' => $this->currency,
            'establishment' => $this->establishment,
            'popularity' => $this->popularity,
            // the visible notes, in the current language
            'notes' => $this->notes
                ->reject(fn (RestaurantNote $note) => $note->is_hidden)
                ->map(fn (RestaurantNote $note) => $note->text)
                ->filter()
                ->values()
                ->all(),
            /* @phpstan-ignore-next-line */
            'media' => new MediaCollection($this->media->load('variants')),
            'schedules' => new ScheduleCollection($this->schedules),
            // special days, which haven't passed yet (yesterday's hours may go on after midnight)
            'exceptions' => ScheduleExceptionResource::collection($this->upcomingExceptions()),
            'closed_until' => $this->closed_until?->format('Y-m-d'),
            'closed_reason' => $this->closed_reason ?: null,
            'brand_primary' => $this->brand_primary,
            'brand_primary_content' => $this->brand_primary_content,
        ];
    }

    /**
     * Special days of the restaurant from yesterday on, in its time zone.
     *
     * @return array<int, ScheduleException>
     */
    protected function upcomingExceptions(): array
    {
        $yesterday = Carbon::now($this->timezone ?: config('app.timezone'))->subDay()->format('Y-m-d');

        return $this->scheduleExceptions
            ->filter(fn (ScheduleException $exception) => $exception->ends_on->format('Y-m-d') >= $yesterday)
            ->values()
            ->all();
    }

    /**
     * @OA\Schema(
     *   schema="Restaurant",
     *   description="Restaurant resource object",
     *   required = {"id", "type", "slug", "name", "country", "city", "place",
     *     "phone", "email", "website", "location", "timezone", "timezone_offset",
     *     "popularity", "locale", "currency", "establishment", "notes", "media", "schedules",
     *     "exceptions", "closed_until", "closed_reason", "brand_primary", "brand_primary_content"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="type", type="string", example="restaurants"),
     *   @OA\Property(property="slug", type="string", example="first"),
     *   @OA\Property(property="name", type="string", example="First"),
     *   @OA\Property(property="country", type="string", example="Ukraine"),
     *   @OA\Property(property="city", type="string", example="Uzhhorod"),
     *   @OA\Property(property="place", type="string", example="Koryatovycha Square, 1а"),
     *   @OA\Property(property="full_address", type="string",
     *     example="Koryatovycha Square, 1а, Uzhhorod, Ukraine"),
     *   @OA\Property(property="phone", type="string", nullable=true,
     *     example="+380501234567"),
     *   @OA\Property(property="email", type="string", nullable=true,
     *     example="imperia@email.com"),
     *   @OA\Property(property="location", type="string", nullable=true,
     *      example="https://goo.gl/maps/g7XZq9H712osMLZW9"),
     *   @OA\Property(property="website", type="string", nullable=true,
     *     example="https://app.imperia.pp.ua"),
     *   @OA\Property(property="timezone", type="string", example="Europe/Kyiv"),
     *   @OA\Property(property="timezone_offset", type="integer", example=180,
     *     description="Selected timezone offset in minutes."),
     *   @OA\Property(property="locale", type="string", nullable=true, example="en"),
     *   @OA\Property(property="currency", type="string", nullable=true, example="uah"),
     *   @OA\Property(property="establishment", type="string", nullable=true, example="restaurant"),
     *   @OA\Property(property="popularity", type="integer", nullable=true, example=1),
     *   @OA\Property(property="notes", type="array", nullable=true, @OA\Items(type="string")),
     *   @OA\Property(property="media", type="array", @OA\Items(ref ="#/components/schemas/Media")),
     *   @OA\Property(property="schedules", type="array", @OA\Items(ref ="#/components/schemas/Schedule")),
     *   @OA\Property(property="exceptions", type="array", description="Special days from yesterday on.",
     *     @OA\Items(ref ="#/components/schemas/ScheduleException")),
     *   @OA\Property(property="closed_until", type="string", format="date", nullable=true,
     *     example="2026-10-14", description="Temporarily closed till this day (inclusive)."),
     *   @OA\Property(property="closed_reason", type="string", nullable=true, example="Renovation"),
     *   @OA\Property(property="brand_primary", type="string", nullable=true, example="#3bb517"),
     *   @OA\Property(property="brand_primary_content", type="string", nullable=true, example="#284625",
     *     description="Color of text on tints of the primary one."),
     * )
     */
}
