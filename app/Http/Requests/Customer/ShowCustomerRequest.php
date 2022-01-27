<?php

namespace App\Http\Requests\Customer;

use App\Http\Requests\Crud\ShowRequest;
use Spatie\QueryBuilder\AllowedInclude;

/**
 * Class ShowCustomerRequest.
 */
class ShowCustomerRequest extends ShowRequest
{
    public function getAllowedIncludes(): array
    {
        return array_merge(
            parent::getAllowedIncludes(),
            [
                AllowedInclude::relationship('family_members', 'familyMembers'),
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
