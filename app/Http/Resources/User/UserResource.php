<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Customer\CustomerResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class UserResource.
 *
 * @mixin User
 */
class UserResource extends JsonResource
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
            'customer_id' => $this->customer_id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
        ];
    }

    /**
     * @OA\Schema(
     *   schema="User",
     *   description="User resource object",
     *   required = {"id", "type", "restaurant_id", "customer_id", "name", "email",
     *      "email_verified_at"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="type", type="string", example="users"),
     *   @OA\Property(property="restaurant_id", type="integer", nullable="true", example="null"),
     *   @OA\Property(property="customer_id", type="integer", nullable="true", example="null"),
     *   @OA\Property(property="name", type="string", example="Admin Admins"),
     *   @OA\Property(property="email", type="string", example="admin@email.com", nullable="true"),
     *   @OA\Property(property="email_verified_at", type="string", format="date-time", nullable="true"),
     *   @OA\Property(property="customer", ref ="#/components/schemas/Customer"),
     * )
     */
}
