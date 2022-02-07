<?php

namespace App\Http\Requests\FamilyMember;

use App\Http\Requests\Crud\IndexRequest;

/**
 * Class IndexFamilyMemberRequest.
 */
class IndexFamilyMemberRequest extends IndexRequest
{
    public function getAllowedIncludes(): array
    {
        return array_merge(
            parent::getAllowedIncludes(),
            [
                'relative',
            ]
        );
    }

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
                //
            ]
        );
    }
}
