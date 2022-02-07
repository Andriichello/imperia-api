<?php

namespace App\Http\Requests\Comment;

use App\Http\Requests\Crud\IndexRequest;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Class IndexCommentRequest.
 */
class IndexCommentRequest extends IndexRequest
{
    public function getAllowedFilters(): array
    {
        return array_merge(
            parent::getAllowedFilters(),
            [
                AllowedFilter::exact('commentable_id'),
                AllowedFilter::exact('commentable_type'),
            ]
        );
    }

    public function getAllowedIncludes(): array
    {
        return array_merge(
            parent::getAllowedIncludes(),
            [
                //
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
