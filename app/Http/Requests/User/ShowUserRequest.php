<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Crud\ShowRequest;

/**
 * Class ShowUserRequest.
 */
class ShowUserRequest extends ShowRequest
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
