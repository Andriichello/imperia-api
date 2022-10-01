<?php

namespace App\Http\Requests\FamilyMember;

use App\Enums\FamilyRelation;
use App\Http\Requests\Crud\UpdateRequest;
use OpenApi\Annotations as OA;

/**
 * Class UpdateFamilyMemberRequest.
 */
class UpdateFamilyMemberRequest extends UpdateRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            [
                'name' => [
                    'string',
                    'min:2',
                ],
                'relation' => [
                    'string',
                    FamilyRelation::getValidationRule(),
                ],
                'birthdate' => [
                    'date',
                    'before:today',
                ],
                'relative_id' => [
                    'integer',
                    'exists:customers,id',
                ],
            ]
        );
    }

    /**
     * @OA\Schema(
     *   schema="UpdateFamilyMemberRequest",
     *   description="Update family member request",
     *   @OA\Property(property="name", type="string", example="Kate"),
     *   @OA\Property(property="relation", type="string", enum={"child", "parent", "grandparent", "partner"}),
     *   @OA\Property(property="birthdate", type="string", format="date", example="1992-10-16"),
     *   @OA\Property(property="relative_id", type="integer", example=1,
     *     description="The id of the customer, who is related to this family member."),
     * )
     */
}
