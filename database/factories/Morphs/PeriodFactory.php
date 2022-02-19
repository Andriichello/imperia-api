<?php

namespace Database\Factories\Morphs;

use App\Models\Morphs\Period;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class PeriodFactory.
 *
 * @method Period|Collection create($attributes = [], ?Model $parent = null)
 */
class PeriodFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = Period::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->unique()->sentence(2),
            'start_at' => $this->faker->dateTimeBetween('-10 days', '-5 days'),
            'end_at' => $this->faker->dateTimeBetween('-4 days', '+10 days'),
            'metadata' => '{}',
        ];
    }

    /**
     * Indicate period metadata.
     *
     * @param Carbon $start
     * @param Carbon $end
     *
     * @return static
     * @throws Exception
     */
    public function withDates(Carbon $start, Carbon $end): static
    {
        if ($start->isAfter($end)) {
            throw new Exception('Start date must be less or equal to end date.');
        }

        return $this->state(
            function (array $attributes) use ($start, $end) {
                $attributes['start_at'] = $start;
                $attributes['end_at'] = $end;
                return $attributes;
            }
        );
    }

    /**
     * Indicate period metadata.
     *
     * @param array $metadata
     *
     * @return static
     */
    public function withMetadata(array $metadata): static
    {
        return $this->state(
            function (array $attributes) use ($metadata) {
                $attributes['metadata'] = json_encode($metadata);
                return $attributes;
            }
        );
    }
}
