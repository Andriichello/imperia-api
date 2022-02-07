<?php

namespace App\Http\Requests\Menu;

use App\Http\Requests\Crud\ShowRequest;

/**
 * Class ShowMenuRequest.
 */
class ShowMenuRequest extends ShowRequest
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
