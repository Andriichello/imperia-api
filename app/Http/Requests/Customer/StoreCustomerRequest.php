<?php

namespace App\Http\Requests\Customer;

use App\Http\Requests\Crud\StoreRequest;

/**
 * Class StoreCustomerRequest.
 */
class StoreCustomerRequest extends StoreRequest
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
                ],
                'surname' => [
                    'required',
                    'string',
                    'min:2',
                ],
                'email' => [
                    'required',
                    'email',
                    'unique:customers,email'
                ],
                'phone' => [
                    'required',
                    'regex:/(\+?[0-9]{1,2})?[0-9]{10,12}/',
                    'unique:customers,phone'
                ],
                'birthdate' => [
                    'required',
                    'date',
                    'before:today'
                ],
            ]
        );
    }

    /**
     * @OA\Schema(
     *   schema="StoreCustomerRequest",
     *   description="Store customer request",
     *   required={"name", "surname", "email", "phone", "birthdate"},
     *   @OA\Property(property="name", type="string", example="John"),
     *   @OA\Property(property="surname", type="string", example="Forrester"),
     *   @OA\Property(property="email", type="string", example="ben.forrester@email.com"),
     *   @OA\Property(property="phone", type="string", example="+380507777777",
     *     description="Phone number may start with a plus and must contain only digits 0-9."),
     *   @OA\Property(property="birthdate", type="string", format="date", example="1992-10-16"),
     * )
     */
}
