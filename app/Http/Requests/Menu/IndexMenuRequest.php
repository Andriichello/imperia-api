<?php

namespace App\Http\Requests\Menu;

use App\Http\Filters\RestaurantsFilter;
use App\Http\Requests\Crud\IndexRequest;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Class IndexMenuRequest.
 */
class IndexMenuRequest extends IndexRequest
{
    public function getAllowedIncludes(): array
    {
        return array_merge(
            parent::getAllowedIncludes(),
            [
                'products',
            ]
        );
    }

    public function getAllowedFilters(): array
    {
        return array_merge(
            parent::getAllowedFilters(),
            [
                AllowedFilter::custom(
                    'restaurants',
                    new RestaurantsFilter('restaurant_menu', 'menu_id')
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
