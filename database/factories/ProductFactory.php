<?php

namespace Database\Factories;

use App\Models\Menu;
use App\Models\Product;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class ProductFactory.
 *
 * @method Product|Collection create($attributes = [], ?Model $parent = null)
 */
class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = Product::class;

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
            'price' => $this->faker->randomFloat(2, 10, 100),
            'weight' => $this->faker->randomFloat(2, 100, 1000),
            'popularity' => rand(0, 100),
        ];
    }

    /**
     * Indicate product's restaurant.
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
