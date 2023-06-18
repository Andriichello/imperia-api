<?php

namespace App\Http\Requests\RestaurantReview;

use App\Http\Requests\Crud\IndexRequest;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder as SpatieBuilder;

/**
 * Class IndexRestaurantReviewRequest.
 */
class IndexRestaurantReviewRequest extends IndexRequest
{
    /**
     * Get sorts fields for spatie query builder.
     *
     * @return array
     */
    public function getAllowedSorts(): array
    {
        return array_merge(
            parent::getAllowedSorts(),
            [
                AllowedSort::field('created_at'),
            ]
        );
    }

    public function getAllowedFilters(): array
    {
        return array_merge(
            parent::getAllowedFilters(),
            [
                AllowedFilter::exact('restaurant_id'),
                AllowedFilter::exact('ip'),
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
            ->defaultSort('-created_at');
    }
}
