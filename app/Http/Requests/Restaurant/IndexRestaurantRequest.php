<?php

namespace App\Http\Requests\Restaurant;

use App\Http\Requests\Crud\IndexRequest;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Class IndexRestaurantRequest.
 */
class IndexRestaurantRequest extends IndexRequest
{
    public function getAllowedFilters(): array
    {
        return array_merge(
            parent::getAllowedFilters(),
            [
                AllowedFilter::exact('slug'),
                AllowedFilter::partial('name'),
            ]
        );
    }

    public function getAllowedIncludes(): array
    {
        return array_merge(
            parent::getAllowedIncludes(),
            [
                'schedules',
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
