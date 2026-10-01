<?php

namespace Database\Factories\Morphs;

use App\Models\BaseModel;
use App\Models\Morphs\Alteration;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class AlterationFactory.
 *
 * @method Alteration|Collection create($attributes = [], ?Model $parent = null)
 */
class AlterationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Alteration::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'metadata' => '{}',
        ];
    }

    /**
     * Indicate alteration values.
     *
     * @param array|object $values
     *
     * @return static
     */
    public function withValues(array|object $values): static
    {
        return $this->state(
            function (array $attributes) use ($values) {
                $attributes['metadata'] = json_encode($values);
                return $attributes;
            }
        );
    }

    /**
     * Indicate alterable model.
     *
     * @param BaseModel $model
     *
     * @return static
     */
    public function withModel(BaseModel $model): static
    {
        return $this->state(
            function (array $attributes) use ($model) {
                $attributes['alterable_id'] = $model->id;
                $attributes['alterable_type'] = $model->getMorphClass();
                return $attributes;
            }
        );
    }

    /**
     * Indicate when the alteration should be performed.
     *
     * @param DateTimeInterface|null $date
     *
     * @return static
     */
    public function performAt(?DateTimeInterface $date): static
    {
        return $this->state(['perform_at' => $date]);
    }
}
