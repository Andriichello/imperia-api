<?php

namespace App\Http\Resources\FamilyMember;

use App\Http\Resources\Customer\CustomerResource;
use App\Models\Customer;
use App\Models\FamilyMember;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class FamilyMemberResource.
 *
 * @mixin FamilyMember
 */
class FamilyMemberResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'relation' => $this->relation,
            'birthdate' => $this->birthdate,
            'relative_id' => $this->relative_id,
            'relative' => new CustomerResource($this->whenLoaded('relative')),
        ];
    }
}
