<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Crud\StoreRequest;

/**
 * Class StoreUserRequest.
 */
class StoreUserRequest extends StoreRequest
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
                'name' => [
                    'required',
                    'string',
                    'min:2',
                    'max:100',
                ],
                'email' => [
                    'required',
                    'email',
                    'unique:users',
                ],
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'max:255',
                ],
            ]
        );
    }
}
