<?php

namespace App\Http\Resources\Waiter;

use App\Http\Resources\Restaurant\RestaurantResource;
use App\Models\Waiter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class WaiterResource.
 *
 * @mixin Waiter
 */
class WaiterResource extends JsonResource
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
            'restaurant_id' => $this->restaurant_id,
            'type' => $this->type,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'surname' => $this->surname,
            'about' => $this->about,
            'restaurant' => new RestaurantResource($this->whenLoaded('restaurant')),
        ];
    }

    /**
     * @OA\Schema(
     *   schema="Waiter",
     *   description="Waiter resource object",
     *   required = {"id", "restaurant_id", "type", "uuid", "name", "surname", "about"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="restaurant_id", type="integer", nullable=true, example=1),
     *   @OA\Property(property="type", type="string", example="waiters"),
     *   @OA\Property(property="uuid", type="string", nullable=true),
     *   @OA\Property(property="name", type="string", example="John"),
     *   @OA\Property(property="surname", type="string", example="Doe"),
     *   @OA\Property(property="about", type="string", nullable=true),
     *   @OA\Property(property="restaurant", ref ="#/components/schemas/Restaurant"),
     * )
     */
}
