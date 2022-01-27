<?php

namespace Database\Factories;

use App\Models\Customer;
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
}
