<?php

namespace Database\Factories\Morphs;

use App\Models\BaseModel;
use App\Models\Morphs\Period;
use App\Models\Morphs\Periodical;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class PeriodicalFactory.
 *
 * @method Periodical|Collection create($attributes = [], ?Model $parent = null)
 */
class PeriodicalFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = Periodical::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'period_id' => Period::factory()
        ];
    }

    /**
     * Indicate period.
     *
     * @param Period $period
     *
     * @return static
     */
    public function withPeriod(Period $period): static
    {
        return $this->state(
            function (array $attributes) use ($period) {
                $attributes['period_id'] = $period->id;
                return $attributes;
            }
        );
    }

    /**
     * Indicate periodical model.
     *
     * @param BaseModel $model
     *
     * @return static
     */
    public function withModel(BaseModel $model): static
    {
        return $this->state(
            function (array $attributes) use ($model) {
                $attributes['periodical_id'] = $model->id;
                $attributes['periodical_type'] = $model->type;
                return $attributes;
            }
        );
    }
}
