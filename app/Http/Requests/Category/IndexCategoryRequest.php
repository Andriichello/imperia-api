<?php

namespace App\Http\Requests\Category;

use App\Http\Filters\RestaurantsFilter;
use App\Http\Requests\Crud\IndexRequest;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Class IndexCategoryRequest.
 */
class IndexCategoryRequest extends IndexRequest
{
    public function getAllowedFilters(): array
    {
        return array_merge(
            parent::getAllowedFilters(),
            [
                AllowedFilter::exact('target'),
                AllowedFilter::custom(
                    'restaurants',
                    new RestaurantsFilter('restaurant_category', 'category_id')
                ),
            ]
        );
    }

    public function getAllowedIncludes(): array
    {
        return array_merge(
            parent::getAllowedIncludes(),
            [
                //
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
