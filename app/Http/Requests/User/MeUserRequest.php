<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;

/**
 * Class MeUserRequest.
 */
class MeUserRequest extends BaseRequest
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
