<?php

namespace App\Http\Requests\Customer;

use App\Http\Filters\CustomersSearchFilter;
use App\Http\Requests\Crud\IndexRequest;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder as SpatieBuilder;

/**
 * Class IndexCustomerRequest.
 */
class IndexCustomerRequest extends IndexRequest
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
                AllowedSort::field('name'),
                AllowedSort::field('surname'),
                AllowedSort::field('updated_at'),
                AllowedSort::field('created_at'),
            ]
        );
    }

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
            ->defaultSort('-updated_at');
    }
}
