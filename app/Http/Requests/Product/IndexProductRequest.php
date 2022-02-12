<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\Crud\IndexRequest;

/**
 * Class IndexProductRequest.
 */
class IndexProductRequest extends IndexRequest
{
    public function getAllowedIncludes(): array
    {
        return array_merge(
            parent::getAllowedIncludes(),
            [
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
