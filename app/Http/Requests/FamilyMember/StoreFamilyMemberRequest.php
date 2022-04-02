<?php

namespace App\Http\Requests\FamilyMember;

use App\Enums\FamilyRelation;
use App\Http\Requests\Crud\StoreRequest;

/**
 * Class StoreFamilyMemberRequest.
 */
class StoreFamilyMemberRequest extends StoreRequest
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
                    'required',
                    'string',
                    'min:2',
                ],
                'relation' => [
                    'nullable',
                    'string',
                    FamilyRelation::getValidationRule(),
                ],
                'birthdate' => [
                    'required',
                    'date',
                    'before:today',
                ],
                'relative_id' => [
                    'required',
                    'integer',
                    'exists:customers,id',
                ],
            ]
        );
    }

    /**
     * @OA\Schema(
     *   schema="StoreFamilyMemberRequest",
     *   description="Store family member request",
     *   required={"name", "relation", "birthdate", "relative_id"},
     *   @OA\Property(property="name", type="string", example="Kate"),
     *   @OA\Property(property="relation", type="string", nullable="true",
     *     enum={"child", "parent", "grandparent", "partner"}),
     *   @OA\Property(property="birthdate", type="string", format="date", example="1992-10-16"),
     *   @OA\Property(property="relative_id", type="integer", example=1,
     *     description="The id of the customer, who is related to this family member."),
     * )
     */
}
