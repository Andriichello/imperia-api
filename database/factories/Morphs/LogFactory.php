<?php

namespace Database\Factories\Morphs;

use App\Models\BaseModel;
use App\Models\Morphs\Log;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class LogFactory.
 *
 * @method Log|Collection create($attributes = [], ?Model $parent = null)
 */
class LogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = Log::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->word,
            'metadata' => '{}',
        ];
    }

    /**
     * Indicate log metadata.
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

    /**
     * Indicate commentable model.
     *
     * @param BaseModel $model
     *
     * @return static
     */
    public function withModel(BaseModel $model): static
    {
        return $this->state(
            function (array $attributes) use ($model) {
                $attributes['loggable_id'] = $model->id;
                $attributes['loggable_type'] = $model->type;
                return $attributes;
            }
        );
    }
}
