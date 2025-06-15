<?php

namespace App\Http\Requests\Dish;

use App\Http\Requests\Crud\ShowRequest;

/**
 * Class ShowDishRequest.
 */
class ShowDishRequest extends ShowRequest
{
    public function getAllowedIncludes(): array
    {
        return array_merge(
            parent::getAllowedIncludes(),
            [
                'menu',
                'category',
                'variants',
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
