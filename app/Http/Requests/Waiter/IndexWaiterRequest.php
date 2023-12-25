<?php

namespace App\Http\Requests\Waiter;

use App\Http\Filters\RestaurantsFilter;
use App\Http\Filters\WaiterSearchFilter;
use App\Http\Requests\Crud\IndexRequest;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Class IndexWaiterRequest.
 */
class IndexWaiterRequest extends IndexRequest
{
    public function getAllowedFilters(): array
    {
        return array_merge(
            parent::getAllowedFilters(),
            [
                AllowedFilter::custom('restaurants', new RestaurantsFilter()),
                AllowedFilter::custom('search', new WaiterSearchFilter()),
                AllowedFilter::partial('uuid'),
                AllowedFilter::partial('name'),
                AllowedFilter::partial('surname'),
                AllowedFilter::partial('email'),
                AllowedFilter::partial('phone'),
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
