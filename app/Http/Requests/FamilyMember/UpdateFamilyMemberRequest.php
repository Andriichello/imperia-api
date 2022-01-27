<?php

namespace App\Http\Requests\FamilyMember;

use App\Enums\FamilyRelation;
use App\Http\Requests\Crud\UpdateRequest;

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
}
