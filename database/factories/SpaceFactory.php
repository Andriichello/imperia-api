<?php

namespace Database\Factories;

use App\Models\Restaurant;
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
            'title' => $this->faker->unique()->sentence(2),
            'description' => $this->faker->sentence(5),
            'number' => rand(1, 10),
            'floor' => rand(1, 3),
            'price' => $this->faker->randomFloat(2, 0, 100),
            'popularity' => rand(0, 100),
        ];
    }

    /**
     * Indicate space's restaurant.
     *
     * @param Restaurant|int|null $restaurant
     *
     * @return static
     */
    public function withRestaurant(Restaurant|int|null $restaurant): static
    {
        return $this->state(
            function (array $attributes) use ($restaurant) {
                $attributes['restaurant_id'] = is_int($restaurant)
                    ? $restaurant : $restaurant?->id;

                return $attributes;
            }
        );
    }
}
