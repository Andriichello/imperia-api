<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class ServiceFactory.
 *
 * @method Service|Collection create($attributes = [], ?Model $parent = null)
 */
class ServiceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = Service::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $prices = [0.0, $this->faker->randomFloat(2, 10, 200)];
        return [
            'title' => $this->faker->unique()->sentence(2),
            'description' => $this->faker->sentence(5),
            'once_paid_price' => $this->faker->randomElement($prices),
            'hourly_paid_price' => $this->faker->randomFloat(2, 10, 100),
        ];
    }
}
