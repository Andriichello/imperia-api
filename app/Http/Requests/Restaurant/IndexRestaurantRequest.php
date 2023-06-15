<?php

namespace App\Http\Requests\Restaurant;

use App\Http\Requests\Crud\IndexRequest;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder as SpatieBuilder;

/**
 * Class IndexRestaurantRequest.
 */
class IndexRestaurantRequest extends IndexRequest
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
