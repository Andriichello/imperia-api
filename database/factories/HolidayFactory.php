<?php

namespace Database\Factories;

use App\Models\Holiday;
use App\Models\Restaurant;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class HolidayFactory.
 *
 * @method Holiday|Collection create($attributes = [], ?Model $parent = null)
 */
class HolidayFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = Holiday::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'restaurant_id' => null,
        ];
    }

    /**
     * Indicate day.
     *
     * @param int $day
     *
     * @return static
     */
    public function withDay(int $day): static
    {
        return $this->state(
            function (array $attributes) use ($day) {
                $attributes['day'] = $day;
                return $attributes;
            }
        );
    }

    /**
     * Indicate month.
     *
     * @param int|null $month
     *
     * @return static
     */
    public function withMonth(?int $month): static
    {
        return $this->state(
            function (array $attributes) use ($month) {
                $attributes['month'] = $month;
                return $attributes;
            }
        );
    }

    /**
     * Indicate year.
     *
     * @param int|null $year
     *
     * @return static
     */
    public function withYear(?int $year): static
    {
        return $this->state(
            function (array $attributes) use ($year) {
                $attributes['year'] = $year;
                return $attributes;
            }
        );
    }

    /**
     * Indicate date.
     *
     * @param CarbonInterface $date
     *
     * @return static
     */
    public function withDate(CarbonInterface $date): static
    {
        return $this->state(
            function (array $attributes) use ($date) {
                $attributes['day'] = $date->day;
                $attributes['month'] = $date->month;
                $attributes['year'] = $date->year;
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
