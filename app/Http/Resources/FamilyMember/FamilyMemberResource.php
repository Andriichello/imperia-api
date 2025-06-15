<?php

namespace App\Http\Resources\FamilyMember;

use App\Http\Resources\Customer\CustomerResource;
use App\Models\FamilyMember;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

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
            'name' => $this->name,
            'relation' => $this->relation,
            'birthdate' => $this->birthdate,
            'relative_id' => $this->relative_id,
            'relative' => new CustomerResource($this->whenLoaded('relative')),
        ];
    }

    /**
     * @OA\Schema(
     *   schema="FamilyMember",
     *   description="Family member resource object",
     *   required = {"id", "type", "name", "relation", "birthdate", "relative_id"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="type", type="string", example="family-members"),
     *   @OA\Property(property="name", type="string", example="Tommy Doe"),
     *   @OA\Property(property="relation", type="string", enum={"child", "parent", "grandparent", "partner"}),
     *   @OA\Property(property="birthdate", type="string", example="1986-01-26"),
     *   @OA\Property(property="relative_id", type="integer", example=1),
     *   @OA\Property(property="relative", ref ="#/components/schemas/Customer"),
     * )
     */
}
