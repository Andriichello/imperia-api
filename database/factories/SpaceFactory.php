<?php

namespace Database\Factories;

use App\Models\Space;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class SpaceFactory.
 *
 * @method Space|Collection create($attributes = [], ?Model $parent = null)
 */
class SpaceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = Space::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->unique()->sentence(3),
            'description' => $this->faker->sentence(5),
            'number' => rand(1, 10),
            'floor' => rand(1, 3),
            'price' => $this->faker->randomFloat(2, 0, 100),
        ];
    }
}
