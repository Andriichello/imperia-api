<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

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
                'max:50',
                "regex:/^[\p{L} ,.'-]+$/u"
            ],
            'surname' => [
                'required',
                'string',
                'min:2',
                'max:50',
                "regex:/^[\p{L} ,.'-]+$/u"
            ],
            'email' => [
                'required_without:phone',
                'email',
                'unique:users',
                Rule::unique('customers', 'email')
                    ->whereNotNull('user_id'),
            ],
            'phone' => [
                'required_without:email',
                'nullable',
                'regex:/(\+?[0-9]{1,2})?[0-9]{10,12}/',
                'unique:customers,phone',
                Rule::unique('customers', 'phone')
                    ->whereNotNull('user_id'),
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:255',
            ],
            'password_confirmation' => [
                'required',
                'same:password',
            ],
        ];
    }

    /**
     * @OA\Schema(
     *   schema="RegisterRequest",
     *   description="Register user request",
     *   required={"name", "surnane", "email", "password", "password_confirmation"},
     *   @OA\Property(property="name", type="string", example="John"),
     *   @OA\Property(property="surname", type="string", example="Doe"),
     *   @OA\Property(property="email", type="string", example="john.doe@email.com"),
     *   @OA\Property(property="phone", type="string", nullable=true, example=null),
     *   @OA\Property(property="password", type="string", example="pa$$w0rd"),
     *   @OA\Property(property="password_confirmation", type="string", example="pa$$w0rd"),
     * )
     */
}
