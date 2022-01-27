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
                    'required',
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
}
