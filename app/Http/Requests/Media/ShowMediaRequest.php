<?php

namespace App\Http\Requests\Media;

use App\Http\Requests\Crud\ShowRequest;

/**
 * Class ShowMediaRequest.
 */
class ShowMediaRequest extends ShowRequest
{
    public function getAllowedIncludes(): array
    {
        return array_merge(
            parent::getAllowedIncludes(),
            [
                'variants',
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
