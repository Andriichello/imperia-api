<?php

namespace Database\Factories\Morphs;

use App\Models\BaseModel;
use App\Models\Morphs\Media;
use App\Models\Morphs\Mediable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class MediableFactory.
 *
 * @method Mediable|Collection create($attributes = [], ?Model $parent = null)
 */
class MediableFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = Mediable::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'media_id' => Media::factory()
        ];
    }

    /**
     * Indicate media.
     *
     * @param Media $discount
     *
     * @return static
     */
    public function withMedia(Media $discount): static
    {
        return $this->state(
            function (array $attributes) use ($discount) {
                $attributes['discount_id'] = $discount->id;
                return $attributes;
            }
        );
    }

    /**
     * Indicate mediable model.
     *
     * @param BaseModel $model
     *
     * @return static
     */
    public function withModel(BaseModel $model): static
    {
        return $this->state(
            function (array $attributes) use ($model) {
                $attributes['mediable_id'] = $model->id;
                $attributes['mediable_type'] = $model->type;
                return $attributes;
            }
        );
    }
}
