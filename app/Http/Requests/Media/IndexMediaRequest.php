<?php

namespace App\Http\Requests\Media;

use App\Http\Requests\Crud\IndexRequest;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Class IndexMediaRequest.
 */
class IndexMediaRequest extends IndexRequest
{
    public function getAllowedFilters(): array
    {
        return array_merge(
            parent::getAllowedFilters(),
            [
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
