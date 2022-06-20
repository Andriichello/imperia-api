<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Crud\RestoreRequest;

/**
 * Class RestoreUserRequest.
 */
class RestoreUserRequest extends RestoreRequest
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
