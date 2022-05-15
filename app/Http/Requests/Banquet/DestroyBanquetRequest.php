<?php

namespace App\Http\Requests\Banquet;

use App\Http\Requests\Crud\DestroyRequest;

/**
 * Class DestroyBanquetRequest.
 */
class DestroyBanquetRequest extends DestroyRequest
{
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
