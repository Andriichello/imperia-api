<?php

namespace App\Http\Resources\Schedule;

use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

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
            'restaurant_id' => $this->restaurant_id,
            'type' => $this->type,
            'weekday' => $this->weekday,
            'beg_hour' => $this->beg_hour,
            'beg_minute' => $this->beg_minute,
            'end_hour' => $this->end_hour,
            'end_minute' => $this->end_minute,
            'begs_in' => $this->begs_in,
            'ends_in' => $this->ends_in,
            'is_cross_date' => $this->is_cross_date,
            'closest_date' => $this->closest_date,
            'timezone' => $this->restaurant?->timezone,
            'timezone_offset' => $this->restaurant?->timezone_offset,
            'archived' => $this->archived,
        ];
    }

    /**
     * @OA\Schema(
     *   schema="Schedule",
     *   description="Schedule resource object",
     *   required = {"id", "type", "weekday", "beg_hour", "end_hour", "beg_minute",
     *     "end_minute", "restaurant_id", "begs_in", "ends_in", "is_cross_date",
     *     "closest_date", "timezone", "timezone_offset", "archived"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="type", type="string", example="schedules"),
     *   @OA\Property(property="weekday", type="string", example="monday",
     *     enum={"monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday"}),
     *   @OA\Property(property="beg_hour", type="integer", example=9,
     *     description="Start hour of the day [0 ; 23]."),
     *   @OA\Property(property="end_hour", type="integer", example=23,
     *     description="End hour of the day [0 ; 23]. If less then beg_hour it,
     *   then it meens that it's a cross day schedule"),
     *   @OA\Property(property="beg_minute", type="integer", example=0,
     *     description="Start minute of the day [0 ; 59]."),
     *   @OA\Property(property="end_minute", type="integer", example=30,
     *     description="End minute of the day [0 ; 59]."),
     *   @OA\Property(property="restaurant_id", type="integer", nullable=true, example=null),
     *   @OA\Property(property="begs_in", type="integer", nullable=true, example=30,
     *     description="Number of minutes untill the restaurant starts operating.
     If null then restaurant is already operating on this schedule."),
     *   @OA\Property(property="ends_in", type="integer", nullable=true, example=780,
     *     description="Number of minutes untill the restaurant ends operating."),
     *   @OA\Property(property="is_cross_date", type="boolean", example=false),
     *   @OA\Property(property="closest_date", type="string", format="date-time",
     *     nullable=true, example="2022-01-12 10:00:00",),
     *   @OA\Property(property="timezone", type="string", nullable=true, example="Europe/Kyiv"),
     *   @OA\Property(property="timezone_offset", type="integer", nullable=true, example=3,
     *     description="Minutes offset from UTC, which is already applied to `closest_date`"),
     *   @OA\Property(property="archived", type="boolean", example=false),
     * )
     */
}
