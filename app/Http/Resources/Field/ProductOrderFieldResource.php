<?php

namespace App\Http\Resources\Field;

use App\Http\Resources\Comment\CommentCollection;
use App\Http\Resources\Discount\DiscountCollection;
use App\Models\Orders\ProductOrderField;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class ProductOrderFieldResource.
 *
 * @mixin ProductOrderField
 */
class ProductOrderFieldResource extends JsonResource
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
            'product_id' => $this->product_id,
            'variant_id' => $this->variant_id,
            'batch' => $this->batch,
            'amount' => $this->amount,
            'serve_at' => $this->serve_at,
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
     *   schema="ProductOrderField",
     *   description="Product order field resource object",
     *   required = {"id", "type", "order_id", "product_id", "variant_id",
     *     "batch", "amount", "serve_at", "total", "discounts_amount",
     *     "discounts_percent", "discounted_total"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="type", type="string", example="product-order-fields"),
     *   @OA\Property(property="order_id", type="integer", example=1),
     *   @OA\Property(property="product_id", type="integer", example=1),
     *   @OA\Property(property="variant_id", type="integer", example=1),
     *   @OA\Property(property="batch", type="string", nullable=true, example="iSXC"),
     *   @OA\Property(property="amount", type="integer", example=5),
     *   @OA\Property(property="serve_at", type="string", nullable=true, example="16:50",
     *     description="24-hours format time, HOURS:MINUTES"),
     *   @OA\Property(property="total", type="float", example=125.55),
     *   @OA\Property(property="discounts_amount", type="float", example=25.55),
     *   @OA\Property(property="discounts_percent", type="float", example=12.5),
     *   @OA\Property(property="discounted_total", type="float", example=100),
     *   @OA\Property(property="comments", type="array",
     *     @OA\Items(ref ="#/components/schemas/Comment")),
     *   @OA\Property(property="discounts", type="array",
     *     @OA\Items(ref ="#/components/schemas/Discount")),
     * )
     */
}
