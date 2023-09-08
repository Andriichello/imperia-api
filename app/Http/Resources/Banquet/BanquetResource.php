<?php

namespace App\Http\Resources\Banquet;

use App\Helpers\BanquetHelper;
use App\Http\Resources\Comment\CommentCollection;
use App\Http\Resources\Customer\CustomerResource;
use App\Http\Resources\Discount\DiscountCollection;
use App\Http\Resources\Order\OrderResource;
use App\Http\Resources\User\UserResource;
use App\Models\Banquet;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class BanquetResource.
 *
 * @mixin Banquet
 */
class BanquetResource extends JsonResource
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
        $helper = new BanquetHelper();

        return [
            'id' => $this->id,
            'type' => $this->type,
            'state' => $this->state,
            'available_states' => $helper->availableTransferStates($this->resource),
            'is_editable' => $this->isEditable(),
            'can_edit' => $this->canBeEditedBy($request->user()),
            'title' => $this->title,
            'description' => $this->description,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
            'paid_at' => $this->paid_at,
            'order_id' => $this->order_id,
            'creator_id' => $this->creator_id,
            'customer_id' => $this->customer_id,
            'restaurant_id' => $this->restaurant_id,
            'advance_amount' => $this->advance_amount,
            'totals' => $this->totals,
            'invoice_url' => $this->getInvoiceUrl($request->user()),
            'order' => new OrderResource($this->whenLoaded('order')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'comments' => new CommentCollection($this->whenLoaded('comments')),
            'discounts' => new DiscountCollection($this->whenLoaded('discounts')),
        ];
    }

    /**
     * @OA\Schema(
     *   schema="Banquet",
     *   description="Banquet resource object",
     *   required = {"id", "type", "state", "available_states", "is_editable", "can_edit",
     *     "title", "description", "start_at", "end_at", "paid_at", "advance_amount",
     *     "totals", "invoice_url", "order_id", "creator_id", "customer_id", "restaurant_id"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="type", type="string", example="banquets"),
     *   @OA\Property(property="state", type="string", example="new",
     *     enum={"new", "confirmed", "postponed", "cancelled", "completed"}),
     *   @OA\Property(property="available_states", type="array", @OA\Items(type="string",
     *     example="new", enum={"draft", "new", "processing", "completed", "cancelled"}),
     *     description="List of states, to which banquet may be transferred."),
     *   @OA\Property(property="is_editable", type="boolean", example="true",
     *     description="Determines if banquet can be edited."),
     *   @OA\Property(property="can_edit", type="boolean", example="true",
     *     description="Determines if logged in user can edit this banquet."),
     *   @OA\Property(property="title", type="string", example="Banquet title."),
     *   @OA\Property(property="description", type="string", nullable="true",
     *      example="Banquet description..."),
     *   @OA\Property(property="start_at", type="string", format="date-time"),
     *   @OA\Property(property="end_at", type="string", format="date-time"),
     *   @OA\Property(property="paid_at", type="string", format="date-time", nullable="true"),
     *   @OA\Property(property="advance_amount", type="integer", example=125.55),
     *   @OA\Property(property="totals", nullable="true", ref ="#/components/schemas/OrderTotals"),
     *   @OA\Property(property="invoice_url", type="string", nullable="true",
     *      example="host/path?signature=long-string"),
     *   @OA\Property(property="order_id", type="integer", example=1, nullable="true"),
     *   @OA\Property(property="creator_id", type="integer", example=1),
     *   @OA\Property(property="customer_id", type="integer", example=1),
     *   @OA\Property(property="restaurant_id", type="integer", nullable="true", example=1),
     *   @OA\Property(property="order", ref ="#/components/schemas/Order"),
     *   @OA\Property(property="creator", ref ="#/components/schemas/User"),
     *   @OA\Property(property="customer", ref ="#/components/schemas/Customer"),
     *   @OA\Property(property="comments", type="array",
     *     @OA\Items(ref ="#/components/schemas/Comment")),
     *   @OA\Property(property="discounts", type="array",
     *     @OA\Items(ref ="#/components/schemas/Discount")),
     * )
     */
}
