<?php

namespace App\Http\Requests\Product;

use App\Http\Filters\CategoriesFilter;
use App\Http\Filters\InFilter;
use App\Http\Filters\MenusFilter;
use App\Http\Filters\RestaurantsFilter;
use App\Http\Filters\TagsFilter;
use App\Http\Requests\Crud\IndexRequest;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder as SpatieBuilder;

/**
 * Class IndexProductRequest.
 */
class IndexProductRequest extends IndexRequest
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
                'tags',
                'categories',
                'alterations',
                'pendingAlterations',
                'performedAlterations',
                'variants.alterations',
                'variants.pendingAlterations',
                'variants.performedAlterations',
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
                AllowedFilter::custom('menus', new MenusFilter()),
                AllowedFilter::custom('tags', new TagsFilter()),
                AllowedFilter::custom('categories', new CategoriesFilter()),
                AllowedFilter::custom('restaurants', new RestaurantsFilter()),
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
