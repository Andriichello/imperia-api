<?php

namespace App\Http\Requests\Menu;

use App\Http\Requests\Crud\IndexRequest;

/**
 * Class IndexMenuRequest.
 */
class IndexMenuRequest extends IndexRequest
{
    public function getAllowedIncludes(): array
    {
        return array_merge(
            parent::getAllowedIncludes(),
            [
                'products',
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
