<?php

namespace Database\Factories\Morphs;

use App\Models\Morphs\Category;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class CategoryFactory.
 *
 * @method Category|Collection create($attributes = [], ?Model $parent = null)
 */
class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'slug' => $this->faker->unique()->slug,
            'title' => $this->faker->unique()->sentence(2),
            'target' => null,
            'description' => $this->faker->sentence,
            'popularity' => rand(0, 100),
        ];
    }

    /**
     * Indicate category target.
     *
     * @param string|null $target
     *
     * @return static
     */
    public function withTarget(?string $target): static
    {
        return $this->state(
            function (array $attributes) use ($target) {
                $attributes['target'] = $target;
                return $attributes;
            }
        );
    }

    /**
     * Indicate category's restaurant.
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
