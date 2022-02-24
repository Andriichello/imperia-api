<?php

namespace App\Http\Resources\Field;

use App\Http\Resources\Comment\CommentCollection;
use App\Http\Resources\Discount\DiscountCollection;
use App\Models\Orders\TicketOrderField;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class TicketOrderFieldResource.php.
 *
 * @mixin TicketOrderField
 */
class TicketOrderFieldResource extends JsonResource
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
            'ticket_id' => $this->ticket_id,
            'amount' => $this->amount,
            'total' => $this->total,
            'comments' => new CommentCollection($this->whenLoaded('comments')),
            'discounts' => new DiscountCollection($this->whenLoaded('discounts')),
        ];
    }

    /**
     * @OA\Schema(
     *   schema="TicketOrderField",
     *   description="Ticket order field resource object",
     *   required = {"id", "type", "order_id", "ticket_id", "amount", "total"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="type", type="string", example="ticket-order-fields"),
     *   @OA\Property(property="order_id", type="integer", example=1),
     *   @OA\Property(property="space_id", type="integer", example=1),
     *   @OA\Property(property="amount", type="integer", example=5),
     *   @OA\Property(property="total", type="float", example=125),
     *   @OA\Property(property="comments", type="array",
     *     @OA\Items(ref ="#/components/schemas/Comment")),
     *   @OA\Property(property="discounts", type="array",
     *     @OA\Items(ref ="#/components/schemas/Discount")),
     * )
     */
}
