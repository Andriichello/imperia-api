<?php

namespace App\Http\Requests\Space;

use App\Http\Requests\Crud\ShowRequest;

/**
 * Class ShowSpaceRequest.
 */
class ShowSpaceRequest extends ShowRequest
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
                'start_at' => [
                    'sometimes',
                    'date',
                ],
                'end_at' => [
                    'sometimes',
                    'date',
                    'after_or_equal:start_at',
                ],
            ]
        );
    }
}
