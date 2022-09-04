<?php

namespace App\Http\Resources\Holiday;

use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class HolidayResource.
 *
 * @mixin Holiday
 */
class HolidayResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'day' => $this->day,
            'month' => $this->month,
            'year' => $this->year,
            'restaurant_id' => $this->restaurant_id,
            'closest_date' => $this->closest_date,
        ];
    }

    /**
     * @OA\Schema(
     *   schema="Holiday",
     *   description="Holiday resource object",
     *   required = {"id", "type", "name", "description", "day", "month", "year", "restaurant_id"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="type", type="string", example="customers"),
     *   @OA\Property(property="name", type="string", example="First"),
     *   @OA\Property(property="description", type="string", nullable="true", example="First"),
     *   @OA\Property(property="day", type="integer", example="Ukraine"),
     *   @OA\Property(property="month", type="integer", nullable="true", example="Uzhhorod"),
     *   @OA\Property(property="year", type="integer", nullable="true", example="Koryatovycha Square, 1а"),
     *   @OA\Property(property="restaurant_id", type="integer", nullable="true", example=1),
     *   @OA\Property(property="closest_date", type="string", format="date-time",
     *     nullable="true", example="2022-01-12 10:00:00",),
     * )
     */
}
