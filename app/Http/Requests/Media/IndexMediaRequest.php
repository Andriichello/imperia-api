<?php

namespace App\Http\Requests\Media;

use App\Http\Filters\MediaSearchFilter;
use App\Http\Requests\Crud\IndexRequest;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder as SpatieBuilder;

/**
 * Class IndexMediaRequest.
 */
class IndexMediaRequest extends IndexRequest
{
    public function getAllowedSorts(): array
    {
        return array_merge(
            parent::getAllowedSorts(),
            [
                AllowedSort::field('created_at'),
                AllowedSort::field('updated_at'),
            ]
        );
    }

    public function getAllowedFilters(): array
    {
        return array_merge(
            parent::getAllowedFilters(),
            [
                AllowedFilter::custom('search', new MediaSearchFilter()),
                AllowedFilter::callback('original_id', fn() => null),
                AllowedFilter::partial('name'),
                AllowedFilter::partial('extension'),
                AllowedFilter::partial('folder'),
                AllowedFilter::partial('disk'),
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
        $builder = parent::spatieBuilder($builder);
        $filters = $this->get('filter', []);

        $originalId = data_get($filters, 'original_id');
        if ($originalId === null) {
            $builder->whereNull('original_id');
        }

        return $builder;
    }
}
