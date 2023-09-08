<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class CustomerFactory.
 *
 * @method Customer|Collection create($attributes = [], ?Model $parent = null)
 */
class CustomerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $name = $this->faker->firstName;
        $surname = $this->faker->lastName;
        $email = strtolower($name) . '.' . strtolower($name) . '@email.com';

        return [
            'name' => $name,
            'surname' => $surname,
            'phone' => $this->faker->phoneNumber,
            'email' => $email,
            'birthdate' => $this->faker->dateTimeBetween('-50 years', '-20 years'),
        ];
    }

    /**
     * Indicate that customer should be created from user.
     *
     * @param User $user
     *
     * @return static
     */
    public function fromUser(User $user): static
    {
        return $this->state(
            function (array $attributes) use ($user) {
                [$name, $surname] = splitName($user->name);

                return array_merge(
                    $attributes,
                    [
                        'name' => $name,
                        'surname' => $surname,
                        'email' => $user->email,
                        'user_id' => $user->id,
                    ]
                );
            }
        );
    }

    /**
     * Indicate restaurant.
     *
     * @param Restaurant $restaurant
     *
     * @return static
     */
    public function withRestaurant(Restaurant $restaurant): static
    {
        return $this->state(
            function (array $attributes) use ($restaurant) {
                $attributes['restaurant_id'] = $restaurant->id;

                return $attributes;
            }
        );
    }
}
