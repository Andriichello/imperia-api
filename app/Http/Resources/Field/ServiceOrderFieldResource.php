<?php

namespace App\Http\Resources\Field;

use App\Models\Orders\ServiceOrderField;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class ServiceOrderFieldResource.
 *
 * @mixin ServiceOrderField
 */
class ServiceOrderFieldResource extends JsonResource
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
            'order_id' => $this->order_id,
            'service_id' => $this->service_id,
            'amount' => $this->amount,
            'duration' => $this->duration,
            'total' => $this->total,
        ];
    }

    /**
     * @OA\Schema(
     *   schema="ServiceOrderField",
     *   description="Service order field resource object",
     *   required = {"id", "type", "order_id", "service_id", "amount", "duration", "total"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="type", type="string", example="service-order-fields"),
     *   @OA\Property(property="order_id", type="integer", example=1),
     *   @OA\Property(property="service_id", type="integer", example=1),
     *   @OA\Property(property="amount", type="integer", example=5),
     *   @OA\Property(property="duration", type="integer", example=90),
     *   @OA\Property(property="total", type="float", example=125.55),
     * )
     */
}
