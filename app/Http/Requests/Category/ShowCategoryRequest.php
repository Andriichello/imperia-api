<?php

namespace App\Http\Requests\Category;

use App\Http\Requests\Crud\ShowRequest;

/**
 * Class ShowCategoryRequest.
 */
class ShowCategoryRequest extends ShowRequest
{
    public function getAllowedIncludes(): array
    {
        return array_merge(
            parent::getAllowedIncludes(),
            [
                'tags',
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
