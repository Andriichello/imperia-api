<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class RestaurantFactory.
 *
 * @method Restaurant|Collection create($attributes = [], ?Model $parent = null)
 */
class RestaurantFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = Restaurant::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'slug' => $this->faker->unique()->slug(),
            'name' => $this->faker->unique()->title(),
            'country' => $this->faker->country(),
            'city' => $this->faker->city(),
            'place' => $this->faker->streetAddress(),
            'popularity' => rand(0, 100),
        ];
    }

    /**
     * Indicate slug.
     *
     * @param string $slug
     *
     * @return static
     */
    public function withSlug(string $slug): static
    {
        return $this->state(
            function (array $attributes) use ($slug) {
                $attributes['slug'] = $slug;
                return $attributes;
            }
        );
    }
}
