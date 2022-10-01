<?php

namespace Database\Factories;

use App\Models\Holiday;
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
            'name' => $this->faker->unique()->sentence(2),
            'description' => $this->faker->sentence(5),
            'date' => $this->faker->date(),
        ];
    }

    /**
     * Indicate commentable model.
     *
     * @param CarbonInterface $date
     *
     * @return static
     */
    public function withDate(CarbonInterface $date): static
    {
        return $this->state(
            function (array $attributes) use ($date) {
                $attributes['date'] = $date;
                return $attributes;
            }
        );
    }
}
