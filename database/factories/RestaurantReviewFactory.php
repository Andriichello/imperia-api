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
     * @var class-string<RestaurantReview>
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
            'rating' => rand(1, 5),
            'name' => $this->faker->firstName(),
            'text' => $this->faker->sentence(10),
            'locale' => 'en',
            'ip_hash' => RestaurantReview::hash($this->faker->ipv4()),
            'client_hash' => RestaurantReview::hash($this->faker->uuid()),
        ];
    }

    /**
     * Indicate the review is public.
     *
     * @return static
     */
    public function approved(): static
    {
        return $this->state(['status' => RestaurantReview::STATUS_APPROVED, 'moderated_at' => now()]);
    }

    /**
     * Indicate the review is never public.
     *
     * @return static
     */
    public function rejected(): static
    {
        return $this->state(['status' => RestaurantReview::STATUS_REJECTED, 'moderated_at' => now()]);
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
