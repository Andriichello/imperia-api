<?php

namespace App\Http\Resources\Customer;

use App\Http\Resources\Comment\CommentCollection;
use App\Http\Resources\FamilyMember\FamilyMemberCollection;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'type' => $this->type,
            'name' => $this->name,
            'surname' => $this->surname,
            'email' => $this->email,
            'phone' => $this->phone,
            'birthdate' => $this->birthdate,
            'family_members' => new FamilyMemberCollection($this->whenLoaded('familyMembers')),
            'comments' => new CommentCollection($this->whenLoaded('comments')),
        ];
    }
}
