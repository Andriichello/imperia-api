<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\Product\ProductCollection;
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
            'spaces' => new ProductCollection($this->whenLoaded('spaces')),
            'tickets' => new ProductCollection($this->whenLoaded('tickets')),
            'products' => new ProductCollection($this->whenLoaded('products')),
            'services' => new ProductCollection($this->whenLoaded('services')),
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
     *   @OA\Property(property="spaces", type="array", @OA\Items(ref ="#/components/schemas/SpaceOrderField")),
     *   @OA\Property(property="tickets", type="array", @OA\Items(ref ="#/components/schemas/TicketOrderField")),
     *   @OA\Property(property="products", type="array", @OA\Items(ref ="#/components/schemas/ProductOrderField")),
     *   @OA\Property(property="services", type="array", @OA\Items(ref ="#/components/schemas/ServiceOrderField")),
     * )
     */
}
