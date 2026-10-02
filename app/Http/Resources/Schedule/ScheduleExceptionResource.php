<?php

namespace App\Http\Resources\Schedule;

use App\Models\ScheduleException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class ScheduleExceptionResource.
 *
 * A special day of the public page (holiday, short day), in the current language.
 *
 * @mixin ScheduleException
 */
class ScheduleExceptionResource extends JsonResource
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
        return [
            'id' => $this->id,
            'starts_on' => $this->starts_on->format('Y-m-d'),
            'ends_on' => $this->ends_on->format('Y-m-d'),
            'is_closed' => $this->is_closed,
            'beg_hour' => $this->beg_hour,
            'beg_minute' => $this->beg_minute,
            'end_hour' => $this->end_hour,
            'end_minute' => $this->end_minute,
            'reason' => $this->reason ?: null,
        ];
    }

    /**
     * @OA\Schema(
     *   schema="ScheduleException",
     *   description="Special day (or days), which overrides the weekly hours.",
     *   required={"id", "starts_on", "ends_on", "is_closed", "beg_hour", "beg_minute", "end_hour",
     *     "end_minute", "reason"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="starts_on", type="string", format="date", example="2026-12-24"),
     *   @OA\Property(property="ends_on", type="string", format="date", example="2026-12-24"),
     *   @OA\Property(property="is_closed", type="boolean", example=false),
     *   @OA\Property(property="beg_hour", type="integer", nullable=true, example=10),
     *   @OA\Property(property="beg_minute", type="integer", nullable=true, example=0),
     *   @OA\Property(property="end_hour", type="integer", nullable=true, example=18),
     *   @OA\Property(property="end_minute", type="integer", nullable=true, example=0),
     *   @OA\Property(property="reason", type="string", nullable=true, example="Christmas Eve"),
     * ),
     */
}
