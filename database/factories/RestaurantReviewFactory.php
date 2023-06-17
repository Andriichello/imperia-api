<?php

namespace Database\Factories;

use App\Models\Restaurant;
use App\Models\RestaurantReview;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class RestaurantReviewFactory.
 *
 * @method RestaurantReview|Collection create($attributes = [], ?Model $parent = null)
 */
class RestaurantReviewFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = RestaurantReview::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'ip' => $this->faker->ipv4(),
            'reviewer' => $this->faker->name,
            'score' => rand(1, 5),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->sentence(10),
        ];
    }

    /**
     * Indicate review's restaurant.
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
