<?php

namespace App\Http\Resources\Discount;

use App\Models\Morphs\Discount;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class DiscountResource.
 *
 * @mixin Discount
 */
class DiscountResource extends JsonResource
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
            'target' => $this->target,
            'title' => $this->title,
            'description' => $this->description,
            'amount' => $this->amount,
            'percent' => $this->percent,
        ];
    }

    /**
     * @OA\Schema(
     *   schema="Discount",
     *   description="Discount resource object",
     *   required = {"id", "type", "target", "title", "description", "amount", "percent"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="type", type="string", example="discounts"),
     *   @OA\Property(property="target", type="string", example="orders", nullable=true),
     *   @OA\Property(property="title", type="string", example="Discount title."),
     *   @OA\Property(property="description", type="string", example="Discount description...", nullable=true),
     *   @OA\Property(property="amount", type="float", example=125.55, nullable=true),
     *   @OA\Property(property="percent", type="float", example=25.5, nullable=true),
     * )
     */
}
