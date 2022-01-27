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
}
