<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Crud\IndexRequest;

/**
 * Class IndexUserRequest.
 */
class IndexUserRequest extends IndexRequest
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
