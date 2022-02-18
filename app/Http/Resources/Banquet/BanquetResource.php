<?php

namespace App\Http\Resources\Banquet;

use App\Http\Resources\Customer\CustomerResource;
use App\Http\Resources\User\UserResource;
use App\Models\Banquet;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
        return [
            'id' => $this->id,
            'type' => $this->type,
            'state' => $this->state,
            'title' => $this->title,
            'description' => $this->description,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
            'paid_at' => $this->paid_at,
            'order_id' => $this->order_id,
            'creator_id' => $this->creator_id,
            'customer_id' => $this->customer_id,
            'advance_amount' => $this->advance_amount,
            'total' => $this->total,
            'creator' => new UserResource($this->whenLoaded('creator')),
            'customer' => new CustomerResource($this->whenLoaded('customer')),
        ];
    }

    /**
     * @OA\Schema(
     *   schema="Banquet",
     *   description="Banquet resource object",
     *   required = {"id", "type", "title", "description", "start_at", "end_at", "paid_at",
     *      "advance_ampunt", "total", "order_id", "creator_id", "customer_id"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="type", type="string", example="orders"),
     *   @OA\Property(property="state", type="string", example="draft",
     *     enum={"draft", "new", "processing", "completed", "cancelled"}),
     *   @OA\Property(property="title", type="string", example="Banquet title."),
     *   @OA\Property(property="description", type="string", example="Banquet description..."),
     *   @OA\Property(property="start_at", type="string", format="date-time"),
     *   @OA\Property(property="end_at", type="string", format="date-time"),
     *   @OA\Property(property="paid_at", type="string", format="date-time", nullable="true"),
     *   @OA\Property(property="advance_amount", type="integer", example=125.55),
     *   @OA\Property(property="total", type="float", example=125.55),
     *   @OA\Property(property="order_id", type="integer", example=1, nullable="true"),
     *   @OA\Property(property="creator_id", type="integer", example=1),
     *   @OA\Property(property="customer_id", type="integer", example=1),
     *   @OA\Property(property="creator", ref ="#/components/schemas/User"),
     *   @OA\Property(property="customer", ref ="#/components/schemas/Customer"),
     * )
     */
}
