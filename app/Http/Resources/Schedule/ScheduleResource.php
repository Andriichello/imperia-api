<?php

namespace App\Http\Resources\Schedule;

use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class ScheduleResource.
 *
 * @mixin Schedule
 */
class ScheduleResource extends JsonResource
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
            'weekday' => $this->weekday,
            'beg_hour' => $this->beg_hour,
            'end_hour' => $this->end_hour,
            'restaurant_id' => $this->restaurant_id,
        ];
    }

    /**
     * @OA\Schema(
     *   schema="Schedule",
     *   description="Schedule resource object",
     *   required = {"id", "type", "weekday", "beg_hour", "end_hour", "restaurant_id"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="type", type="string", example="customers"),
     *   @OA\Property(property="weekday", type="string", example="monday",
     *     enum={"monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday"}),
     *   @OA\Property(property="beg_hour", type="integer", example=9,
     *     description="Start hour of the day [0 ; 23]."),
     *   @OA\Property(property="end_hour", type="integer", example=23,
     *     description="Start hour of the day [0 ; 23]. If less then beg_hour it,
     *   then it meens that it's a cross day schedule"),
     *   @OA\Property(property="restaurant_id", type="integer", nullable="true", example=null),
     * )
     */
}
