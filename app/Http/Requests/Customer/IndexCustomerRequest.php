<?php

namespace App\Http\Requests\Customer;

use App\Http\Filters\CustomersSearchFilter;
use App\Http\Requests\Crud\IndexRequest;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;

/**
 * Class IndexCustomerRequest.
 */
class IndexCustomerRequest extends IndexRequest
{
    public function getAllowedFilters(): array
    {
        return array_merge(
            parent::getAllowedFilters(),
            [
                AllowedFilter::custom('search', new CustomersSearchFilter()),
                AllowedFilter::partial('name'),
                AllowedFilter::partial('surname'),
                AllowedFilter::partial('email'),
                AllowedFilter::partial('phone'),
            ]
        );
    }

    public function getAllowedIncludes(): array
    {
        return array_merge(
            parent::getAllowedIncludes(),
            [
                'comments',
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
