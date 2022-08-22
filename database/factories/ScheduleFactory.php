<?php

namespace Database\Factories;

use App\Enums\Weekday;
use App\Models\Restaurant;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class ScheduleFactory.
 *
 * @method Schedule|Collection create($attributes = [], ?Model $parent = null)
 */
class ScheduleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = Schedule::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'beg_hour' => rand(0, 11),
            'end_hour' => rand(12, 23),
            'weekday' => $this->faker->randomElement(Weekday::getValues()),
            'restaurant_id' => null,
        ];
    }

    /**
     * Indicate weekday.
     *
     * @param string $weekday
     *
     * @return static
     */
    public function withWeekday(string $weekday): static
    {
        return $this->state(
            function (array $attributes) use ($weekday) {
                $attributes['weekday'] = $weekday;
                return $attributes;
            }
        );
    }

    /**
     * Indicate restaurant.
     *
     * @param Restaurant|null $restaurant
     *
     * @return static
     */
    public function withRestaurant(?Restaurant $restaurant): static
    {
        return $this->state(
            function (array $attributes) use ($restaurant) {
                $attributes['restaurant_id'] = $restaurant?->id;
                return $attributes;
            }
        );
    }
}
