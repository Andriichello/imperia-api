<?php

namespace App\Http\Requests\Crud;

use App\Http\Requests\CrudRequest;

/**
 * Class StoreRequest.
 */
class StoreRequest extends CrudRequest
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
