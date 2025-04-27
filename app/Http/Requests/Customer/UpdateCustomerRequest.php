<?php

namespace App\Http\Requests\Customer;

use App\Http\Requests\Crud\UpdateRequest;
use App\Models\Customer;
use App\Models\Morphs\Comment;
use App\Models\Restaurant;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

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
            Comment::rulesForAttaching(),
            [
                'restaurant_id' => [
                    'integer',
                    Rule::exists(
                        Restaurant::class,
                        'id'
                    ),
                ],
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
                ],
                'phone' => [
                    'regex:/(\+?[0-9]{1,2})?[0-9]{10,12}/',
                ],
                'birthdate' => [
                    'date',
                    'before:today'
                ],
            ]
        );
    }

    /**
     * Get form request fields' default values.
     *
     * @return array
     */
    protected function defaults(): array
    {
        $user = $this->user();

        $defaults = [];

        if ($this->missing('restaurant_id')) {
            $defaults['restaurant_id'] = $user->restaurant_id;
        }

        return $defaults;
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function (Validator $validator) {
            $user = $this->user();
            $restaurantId = $this->get('restaurant_id');

            if ($user->restaurant_id && $user->restaurant_id !== $restaurantId) {
                $validator->errors()
                    ->add(
                        'restaurant_id',
                        'The restaurant id must be the same as user\'s.'
                    );
            }

            $email = $this->get('email');

            if ($email) {
                $exists = Customer::query()
                    ->where('id', '!=', $this->id())
                    ->where('email', $email)
                    ->where('restaurant_id', $restaurantId)
                    ->exists();

                if ($exists) {
                    $validator->errors()
                        ->add(
                            'email',
                            "The email must be unique within restaurant (id: $restaurantId)"
                        );
                }
            }

            $phone = $this->get('phone');

            if ($phone) {
                $exists = Customer::query()
                    ->where('id', '!=', $this->id())
                    ->where('phone', $phone)
                    ->where('restaurant_id', $restaurantId)
                    ->exists();

                if ($exists) {
                    $validator->errors()
                        ->add(
                            'phone',
                            "The phone must be unique within restaurant (id: $restaurantId)"
                        );
                }
            }
        });
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        if ($this->isByStaff()) {
            return true;
        }

        $user = $this->user();
        /** @var Customer $customer */
        $customer = $this->target(Customer::class);

        return $user->restaurant_id === null
            || $user->restaurant_id === $customer->restaurant_id;
    }

    /**
     * @OA\Schema(
     *   schema="UpdateCustomerRequest",
     *   description="Update customer request",
     *   @OA\Property(property="restaurant_id", type="integer", nullable=true, example=1),
     *   @OA\Property(property="name", type="string", example="John"),
     *   @OA\Property(property="surname", type="string", example="Forrester"),
     *   @OA\Property(property="email", type="string", example="ben.forrester@email.com"),
     *   @OA\Property(property="phone", type="string", example="+380507777777",
     *     description="Phone number may start with a plus and must contain only digits 0-9."),
     *   @OA\Property(property="birthdate", type="string", format="date", example="1992-10-16"),
     * )
     */
}
