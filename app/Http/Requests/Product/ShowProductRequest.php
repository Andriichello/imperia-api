<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\Crud\ShowRequest;

/**
 * Class ShowProductRequest.
 */
class ShowProductRequest extends ShowRequest
{
    public function getAllowedIncludes(): array
    {
        return array_merge(
            parent::getAllowedIncludes(),
            [
                'tags',
                'categories',
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
