<?php

namespace App\Http\Requests\Comment;

use App\Http\Requests\Crud\IndexRequest;

/**
 * Class IndexCommentRequest.
 */
class IndexCommentRequest extends IndexRequest
{
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
