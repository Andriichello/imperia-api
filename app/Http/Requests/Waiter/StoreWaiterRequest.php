<?php

namespace App\Http\Requests\Waiter;

use App\Http\Requests\Crud\StoreRequest;
use App\Models\Morphs\Comment;
use App\Models\Restaurant;
use App\Models\Waiter;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * Class StoreWaiterRequest.
 */
class StoreWaiterRequest extends StoreRequest
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
                    'required',
                    'integer',
                    Rule::exists(
                        Restaurant::class,
                        'id'
                    ),
                ],
                'uuid' => [
                    'required',
                    'string',
                    'min:1',
                    'max:6',
                ],
                'name' => [
                    'required',
                    'string',
                    'min:2',
                    'max:50',
                ],
                'surname' => [
                    'sometimes',
                    'string',
                    'min:0',
                    'max:50',
                ],
                'email' => [
                    'sometimes',
                    'nullable',
                    'email',
                ],
                'phone' => [
                    'sometimes',
                    'nullable',
                    'regex:/(\+?[0-9]{1,2})?[0-9]{10,12}/',
                ],
                'birthdate' => [
                    'sometimes',
                    'nullable',
                    'date',
                    'before:today'
                ],
                'about' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'min:1',
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

            $uuid = $this->get('uuid');

            if ($uuid) {
                $exists = Waiter::query()
                    ->where('uuid', $uuid)
                    ->where('restaurant_id', $restaurantId)
                    ->exists();

                if ($exists) {
                    $validator->errors()
                        ->add(
                            'uuid',
                            "The uuid must be unique within restaurant (id: $restaurantId)"
                        );
                }
            }

            $email = $this->get('email');

            if ($email) {
                $exists = Waiter::query()
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
                $exists = Waiter::query()
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
     * @OA\Schema(
     *   schema="StoreWaiterRequest",
     *   description="Store waiter request.",
     *   required={"restaurant_id", "uuid", "name", "surname", "email", "phone", "birthdate", "about"},
     *   @OA\Property(property="restaurant_id", type="integer", nullable=true, example=1),
     *   @OA\Property(property="uuid", type="string", nullable=true),
     *   @OA\Property(property="name", type="string", example="John"),
     *   @OA\Property(property="surname", type="string", example="Forrester"),
     *   @OA\Property(property="email", type="string", nullable=true, example="ben.forrester@email.com"),
     *   @OA\Property(property="phone", type="string", nullable=true, example="+380507777777",
     *     description="Phone number may start with a plus and must contain only digits 0-9."),
     *   @OA\Property(property="birthdate", type="string", nullable=true, format="date", example="1992-10-16"),
     *   @OA\Property(property="about", type="string", nullable=true),
     * )
     */
}
