<?php

namespace App\Http\Resources\Tip;

use App\Http\Resources\Restaurant\RestaurantResource;
use App\Models\Morphs\Tip;
use App\Models\Waiter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class TipResource.
 *
 * @mixin Tip
 */
class TipResource extends JsonResource
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
            'amount' => $this->amount,
            'commission' => $this->commission,
            'note' => $this->note,
            'restaurant' => new RestaurantResource($this->whenLoaded('restaurant')),
        ];
    }

    /**
     * @OA\Schema(
     *   schema="Tip",
     *   description="Tip resource object",
     *   required = {"id", "restaurant_id", "type", "amount", "commission", "about"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="restaurant_id", type="integer", nullable="true", example=1),
     *   @OA\Property(property="type", type="string", example="tips"),
     *   @OA\Property(property="amount", type="float", example=25.0),
     *   @OA\Property(property="commission", type="float", nullable="true"),
     *   @OA\Property(property="about", type="string", nullable="true"),
     *   @OA\Property(property="restaurant", ref ="#/components/schemas/Restaurant"),
     * )
     */
}
