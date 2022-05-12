<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Crud\DestroyRequest;

/**
 * Class DestroyUserRequest.
 */
class DestroyUserRequest extends DestroyRequest
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
