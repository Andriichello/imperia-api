<?php

namespace Database\Factories;

use App\Models\Restaurant;
use App\Models\Waiter;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class WaiterFactory.
 *
 * @method Waiter|Collection create($attributes = [], ?Model $parent = null)
 */
class WaiterFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = Waiter::class;

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
