<?php

namespace App\Http\Resources\Holiday;

use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

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
            'date' => $this->date,
            'closest_date' => $this->closest_date,
        ];
    }

    /**
     * @OA\Schema(
     *   schema="Holiday",
     *   description="Holiday resource object",
     *   required = {"id", "type", "name", "description", "date"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="type", type="string", example="holidays"),
     *   @OA\Property(property="name", type="string", example="Random holiday"),
     *   @OA\Property(property="description", type="string", nullable="true", example="null"),
     *   @OA\Property(property="date", type="string", format="date", example="2022-01-12",),
     *   @OA\Property(property="closest_date", type="string", format="date-time",
     *     nullable="true", example="2022-01-12 10:00:00",),
     * )
     */
}
