<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\Comment\CommentCollection;
use App\Http\Resources\Discount\DiscountCollection;
use App\Http\Resources\Field\ProductOrderFieldCollection;
use App\Http\Resources\Field\ServiceOrderFieldCollection;
use App\Http\Resources\Field\SpaceOrderFieldCollection;
use App\Http\Resources\Field\TicketOrderFieldCollection;
use App\Models\Orders\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class OrderResource.
 *
 * @mixin Order
 */
class OrderResource extends JsonResource
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
            'total' => $this->total,
            'banquet_id' => $this->banquet_id,
            'spaces' => new SpaceOrderFieldCollection($this->whenLoaded('spaces')),
            'tickets' => new TicketOrderFieldCollection($this->whenLoaded('tickets')),
            'products' => new ProductOrderFieldCollection($this->whenLoaded('products')),
            'services' => new ServiceOrderFieldCollection($this->whenLoaded('services')),
            'comments' => new CommentCollection($this->whenLoaded('comments')),
            'discounts' => new DiscountCollection($this->whenLoaded('discounts')),
        ];
    }

    /**
     * @OA\Schema(
     *   schema="Order",
     *   description="Order resource object",
     *   required = {"id", "type", "total", "banquet_id"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="type", type="string", example="orders"),
     *   @OA\Property(property="total", type="float", example=125.55),
     *   @OA\Property(property="banquet_id", type="integer", example=1),
     *   @OA\Property(property="spaces", type="array",
     *     @OA\Items(ref ="#/components/schemas/SpaceOrderField")),
     *   @OA\Property(property="tickets", type="array",
     *     @OA\Items(ref ="#/components/schemas/TicketOrderField")),
     *   @OA\Property(property="products", type="array",
     *     @OA\Items(ref ="#/components/schemas/ProductOrderField")),
     *   @OA\Property(property="services", type="array",
     *     @OA\Items(ref ="#/components/schemas/ServiceOrderField")),
     *   @OA\Property(property="comments", type="array",
     *     @OA\Items(ref ="#/components/schemas/Comment")),
     *   @OA\Property(property="discounts", type="array",
     *     @OA\Items(ref ="#/components/schemas/Discount")),
     * )
     */
}
