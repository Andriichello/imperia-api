<?php

namespace Database\Factories\Morphs;

use App\Models\BaseModel;
use App\Models\Morphs\Alteration;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class ChangeFactory.
 *
 * @method Alteration|Collection create($attributes = [], ?Model $parent = null)
 */
class AlterationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
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
            'values' => '{"key":"value"}',
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
                $attributes['values'] = json_encode($values);
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
                $attributes['alterable_type'] = $model->type;
                return $attributes;
            }
        );
    }
}
