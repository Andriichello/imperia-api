<?php

namespace App\Http\Requests\Tag;

use App\Http\Requests\Crud\ShowRequest;

/**
 * Class ShowCategoryRequest.
 */
class ShowTagRequest extends ShowRequest
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
