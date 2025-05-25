<?php

namespace App\Http\Resources\Customer;

use App\Http\Resources\Comment\CommentCollection;
use App\Http\Resources\FamilyMember\FamilyMemberCollection;
use App\Http\Resources\Restaurant\RestaurantResource;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class CustomerResource.
 *
 * @mixin Customer
 */
class CustomerResource extends JsonResource
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
            'name' => $this->name,
            'surname' => $this->surname,
            'email' => $this->email,
            'phone' => $this->phone,
            'birthdate' => $this->birthdate,
            'restaurant' => new RestaurantResource($this->whenLoaded('restaurant')),
            'family_members' => new FamilyMemberCollection($this->whenLoaded('familyMembers')),
            'comments' => new CommentCollection($this->whenLoaded('comments')),
        ];
    }

    /**
     * @OA\Schema(
     *   schema="Customer",
     *   description="Customer resource object",
     *   required = {"id", "restaurant_id", "type", "name", "surname", "email", "phone", "birthdate"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="restaurant_id", type="integer", nullable=true, example=1),
     *   @OA\Property(property="type", type="string", example="customers"),
     *   @OA\Property(property="name", type="string", example="John"),
     *   @OA\Property(property="surname", type="string", example="Doe"),
     *   @OA\Property(property="phone", type="string", example="+380507777777"),
     *   @OA\Property(property="email", type="string", nullable=true, example="john.doe@email.com"),
     *   @OA\Property(property="birthdate", type="string", nullable=true, format="date", example="1986-01-26"),
     *   @OA\Property(property="restaurant", ref ="#/components/schemas/Restaurant"),
     *   @OA\Property(property="family_members", type="array",
     *     @OA\Items(ref ="#/components/schemas/FamilyMember")),
     *   @OA\Property(property="comments", type="array",
     *     @OA\Items(ref ="#/components/schemas/Comment")),
     * )
     */
}
