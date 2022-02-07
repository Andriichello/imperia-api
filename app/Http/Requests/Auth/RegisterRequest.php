<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;

/**
 * Class RegisterRequest.
 */
class RegisterRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
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
        ];
    }

    /**
     * @OA\Schema(
     *   schema="RegisterRequest",
     *   description="Register user request",
     *   required={"name", "email", "password"},
     *   @OA\Property(property="name", type="string", example="John Doe"),
     *   @OA\Property(property="email", type="string", example="john.doe@email.com"),
     *   @OA\Property(property="password", type="string", example="pa$$w0rd")
     * )
     */
}
