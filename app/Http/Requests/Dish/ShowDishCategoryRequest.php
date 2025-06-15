<?php

namespace App\Http\Requests\Dish;

use App\Http\Requests\Crud\ShowRequest;

/**
 * Class ShowDishCategoryRequest.
 */
class ShowDishCategoryRequest extends ShowRequest
{
    public function getAllowedIncludes(): array
    {
        return array_merge(
            parent::getAllowedIncludes(),
            [
                'menu',
                'media',
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
