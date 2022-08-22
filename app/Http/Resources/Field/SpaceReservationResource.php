<?php

namespace App\Http\Resources\Field;

use App\Models\Orders\SpaceOrderField;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class SpaceReservationResource.
 *
 * @mixin SpaceOrderField
 */
class SpaceReservationResource extends JsonResource
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
            'type' => $this->type,
            'space_id' => $this->space_id,
            'order_id' => $this->order_id,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
            'duration' => $this->duration,
        ];
    }

    /**
     * @OA\Schema(
     *   schema="SpaceReservation",
     *   description="Space reservation resource object",
     *   required = {"type", "order_id", "space_id", "start_at", "end_at", "duration"},
     *   @OA\Property(property="type", type="string", example="space-order-fields"),
     *   @OA\Property(property="order_id", type="integer", example=1),
     *   @OA\Property(property="space_id", type="integer", example=1),
     *   @OA\Property(property="start_at", type="string", format="date-time", example="2022-01-12 11:00:00"),
     *   @OA\Property(property="end_at", type="string", format="date-time", example="2022-01-12 23:00:00"),
     *   @OA\Property(property="duration", type="int", example="720"),
     * )
     */
}
