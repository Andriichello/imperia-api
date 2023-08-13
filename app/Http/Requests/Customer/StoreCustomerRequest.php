<?php

namespace App\Http\Requests\Customer;

use App\Http\Requests\Crud\StoreRequest;
use App\Models\Morphs\Comment;
use Illuminate\Contracts\Validation\Validator;
use OpenApi\Annotations as OA;

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
            Comment::rulesForAttaching(),
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
                    'required_without:phone',
                    'email',
                    'unique:customers,email'
                ],
                'phone' => [
                    'required_without:email',
                    'nullable',
                    'regex:/(\+?[0-9]{1,2})?[0-9]{10,12}/',
                    'unique:customers,phone'
                ],
                'birthdate' => [
                    'sometimes',
                    'nullable',
                    'date',
                    'before:today'
                ],
            ]
        );
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function (Validator $validator) {
            $email = $this->get('email');
            $phone = $this->get('phone');

            if (!$email && !$phone) {
                $validator->errors()
                    ->add(
                        'email',
                        'The email or phone must be specified.'
                    )
                    ->add(
                        'phone',
                        'The email or phone must be specified.'
                    );
            }
        });
    }

    /**
     * @OA\Schema(
     *   schema="StoreCustomerRequest",
     *   description="Store customer request. `email` or `phone`
           must be specified.",
     *   required={"name", "surname", "email", "phone", "birthdate"},
     *   @OA\Property(property="name", type="string", example="John"),
     *   @OA\Property(property="surname", type="string", example="Forrester"),
     *   @OA\Property(property="email", type="string", nullable="true", example="ben.forrester@email.com"),
     *   @OA\Property(property="phone", type="string", nullable="true", example="+380507777777",
     *     description="Phone number may start with a plus and must contain only digits 0-9."),
     *   @OA\Property(property="birthdate", type="string", nullable="true", format="date", example="1992-10-16"),
     * )
     */
}
