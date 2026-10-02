<?php

namespace Database\Factories;

use App\Models\Restaurant;
use App\Models\ScheduleException;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Class ScheduleExceptionFactory.
 */
class ScheduleExceptionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<ScheduleException>
     */
    protected $model = ScheduleException::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $date = now()->addDays(rand(1, 60))->startOfDay();

        return [
            'starts_on' => $date,
            'ends_on' => $date,
            'is_closed' => true,
            'reason' => $this->faker->sentence(2),
        ];
    }

    /**
     * Indicate the restaurant of the special day.
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
