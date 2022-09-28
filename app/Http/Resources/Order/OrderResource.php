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
use OpenApi\Annotations as OA;

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
            'totals' => $this->totals,
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
     *   schema="OrderTotals",
     *   description="Order totals resource object",
     *   required = {"all", "spaces", "tickets", "services", "products"},
     *   @OA\Property(property="all", type="float", example=40.04),
     *   @OA\Property(property="spaces", type="float", example=10.01),
     *   @OA\Property(property="tickets", type="float", example=10.01),
     *   @OA\Property(property="services", type="float", example=10.01),
     *   @OA\Property(property="products", type="float", example=10.01)
     * ),
     *
     * @OA\Schema(
     *   schema="Order",
     *   description="Order resource object",
     *   required = {"id", "type", "totals", "banquet_id"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="type", type="string", example="orders"),
     *   @OA\Property(property="totals", ref ="#/components/schemas/OrderTotals"),
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
