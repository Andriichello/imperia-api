<?php

namespace App\Http\Requests\Product;

use App\Http\Filters\CategoriesFilter;
use App\Http\Filters\MenusFilter;
use App\Http\Filters\RestaurantsFilter;
use App\Http\Requests\Crud\IndexRequest;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Class IndexProductRequest.
 */
class IndexProductRequest extends IndexRequest
{
    public function getAllowedIncludes(): array
    {
        return array_merge(
            parent::getAllowedIncludes(),
            [
                'categories',
            ]
        );
    }

    public function getAllowedFilters(): array
    {
        return array_merge(
            parent::getAllowedFilters(),
            [
                AllowedFilter::partial('title'),
                AllowedFilter::custom('menus', new MenusFilter()),
                AllowedFilter::custom('categories', new CategoriesFilter()),
                AllowedFilter::custom(
                    'restaurants',
                    new RestaurantsFilter('restaurant_product', 'product_id')
                ),
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
