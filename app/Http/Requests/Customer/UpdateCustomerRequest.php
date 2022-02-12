<?php

namespace App\Http\Requests\Customer;

use App\Http\Requests\Crud\UpdateRequest;

/**
 * Class UpdateCustomerRequest.
 */
class UpdateCustomerRequest extends UpdateRequest
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
                    'string',
                    'min:2',
                ],
                'surname' => [
                    'string',
                    'min:2',
                ],
                'email' => [
                    'email',
                    'unique:customers,email'
                ],
                'phone' => [
                    'regex:/(\+?[0-9]{1,2})?[0-9]{10,12}/',
                    'unique:customers,phone'
                ],
                'birthdate' => [
                    'date',
                    'before:today'
                ],
            ]
        );
    }

    /**
     * @OA\Schema(
     *   schema="UpdateCustomerRequest",
     *   description="Update customer request",
     *   @OA\Property(property="name", type="string", example="John"),
     *   @OA\Property(property="surname", type="string", example="Forrester"),
     *   @OA\Property(property="email", type="string", example="ben.forrester@email.com"),
     *   @OA\Property(property="phone", type="string", example="+380507777777",
     *     description="Phone number may start with a plus and must contain only digits 0-9."),
     *   @OA\Property(property="birthdate", type="string", format="date", example="1992-10-16"),
     * )
     */
}
