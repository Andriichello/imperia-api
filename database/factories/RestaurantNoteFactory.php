<?php

namespace Database\Factories;

use App\Models\Restaurant;
use App\Models\RestaurantNote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class RestaurantNoteFactory.
 */
class RestaurantNoteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<RestaurantNote>
     */
    protected $model = RestaurantNote::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'text' => $this->faker->sentence(6),
            'is_hidden' => false,
            'order' => 0,
        ];
    }

    /**
     * Indicate the restaurant of the note.
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
