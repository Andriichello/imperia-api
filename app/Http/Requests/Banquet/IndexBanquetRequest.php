<?php

namespace App\Http\Requests\Banquet;

use App\Http\Requests\Crud\IndexRequest;

/**
 * Class IndexBanquetRequest.
 */
class IndexBanquetRequest extends IndexRequest
{
    public function getAllowedIncludes(): array
    {
        return array_merge(
            parent::getAllowedIncludes(),
            [
                'order',
                'creator',
                'customer',
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
