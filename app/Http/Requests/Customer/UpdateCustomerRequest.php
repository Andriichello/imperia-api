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
}
