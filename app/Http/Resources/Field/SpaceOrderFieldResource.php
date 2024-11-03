<?php

namespace App\Http\Resources\Field;

use App\Http\Resources\Comment\CommentCollection;
use App\Http\Resources\Discount\DiscountCollection;
use App\Models\Orders\SpaceOrderField;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class SpaceOrderFieldResource.
 *
 * @mixin SpaceOrderField
 */
class SpaceOrderFieldResource extends JsonResource
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
            'space_id' => $this->space_id,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
            'duration' => $this->duration,
            'total' => $this->total,
            'discounts_amount' => $this->discounts_amount,
            'discounts_percent' => $this->discounts_percent,
            'discounted_total' => $this->discounted_total,
            'comments' => new CommentCollection($this->whenLoaded('comments')),
            'discounts' => new DiscountCollection($this->whenLoaded('discounts')),
        ];
    }

    /**
     * @OA\Schema(
     *   schema="SpaceOrderField",
     *   description="Space order field resource object",
     *   required = {"id", "type", "order_id", "space_id", "start_at", "end_at", "total",
     *     "discounts_amount", "discounts_percent", "discounted_total"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="type", type="string", example="space-order-fields"),
     *   @OA\Property(property="order_id", type="integer", example=1),
     *   @OA\Property(property="space_id", type="integer", example=1),
     *   @OA\Property(property="start_at", type="string", format="date-time", example="2022-01-12 11:00:00"),
     *   @OA\Property(property="end_at", type="string", format="date-time", example="2022-01-12 23:00:00"),
     *   @OA\Property(property="duration", type="int", example=60),
     *   @OA\Property(property="total", type="number", example=159.99),
     *   @OA\Property(property="discounts_amount", type="number", example=25.55),
     *   @OA\Property(property="discounts_percent", type="number", example=12.5),
     *   @OA\Property(property="discounted_total", type="number", example=100),
     *   @OA\Property(property="comments", type="array",
     *     @OA\Items(ref ="#/components/schemas/Comment")),
     *   @OA\Property(property="discounts", type="array",
     *     @OA\Items(ref ="#/components/schemas/Discount")),
     * )
     */
}
