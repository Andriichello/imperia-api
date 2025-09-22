<?php

namespace App\Http\Requests\Dish;

use App\Http\Filters\InFilter;
use App\Http\Requests\Crud\IndexRequest;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder as SpatieBuilder;

/**
 * Class IndexDishRequest.
 */
class IndexDishRequest extends IndexRequest
{
    public function getAllowedSorts(): array
    {
        return array_merge(
            parent::getAllowedSorts(),
            [
                AllowedSort::field('popularity'),
            ]
        );
    }

    public function getAllowedIncludes(): array
    {
        return array_merge(
            parent::getAllowedIncludes(),
            [
                'menu',
                'category',
                'variants',
                'media',
            ]
        );
    }

    public function getAllowedFilters(): array
    {
        return array_merge(
            parent::getAllowedFilters(),
            [
                AllowedFilter::partial('title'),
                AllowedFilter::custom('ids', new InFilter('id')),
                AllowedFilter::exact('menu_id'),
                AllowedFilter::custom('menu_ids', new InFilter('menu_id')),
                AllowedFilter::callback('restaurant_id', function ($query, $value) {
                    $query->whereHas('menu', function ($q) use ($value) {
                        $q->where('restaurant_id', $value);
                    });
                }),
                AllowedFilter::exact('category_id'),
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

    /**
     * Apply allowed options to spatie builder.
     *
     * @param Builder|EloquentBuilder|SpatieBuilder $builder
     *
     * @return SpatieBuilder
     */
    public function spatieBuilder(SpatieBuilder|EloquentBuilder|Builder $builder): SpatieBuilder
    {
        return parent::spatieBuilder($builder)
            ->defaultSort('-popularity');
    }
}
