<?php

namespace App\Http\Requests\Media;

use App\Http\Filters\MediaSearchFilter;
use App\Http\Requests\Crud\IndexRequest;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

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
}
