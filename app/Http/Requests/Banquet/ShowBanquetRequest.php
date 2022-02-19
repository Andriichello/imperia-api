<?php

namespace App\Http\Requests\Banquet;

use App\Http\Requests\Crud\ShowRequest;

/**
 * Class ShowBanquetRequest.
 */
class ShowBanquetRequest extends ShowRequest
{
    public function getAllowedIncludes(): array
    {
        return array_merge(
            parent::getAllowedIncludes(),
            [
                'creator',
                'customer',
                'comments',
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
