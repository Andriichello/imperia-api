<?php

namespace Database\Factories\Morphs;

use App\Models\BaseModel;
use App\Models\Morphs\Tag;
use App\Models\Morphs\Taggable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class TaggableFactory.
 *
 * @method Taggable|Collection create($attributes = [], ?Model $parent = null)
 */
class TaggableFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = Taggable::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'tag_id' => Tag::factory()
        ];
    }

    /**
     * Indicate tag.
     *
     * @param Tag $tag
     *
     * @return static
     */
    public function withTag(Tag $tag): static
    {
        return $this->state(
            function (array $attributes) use ($tag) {
                $attributes['tag_id'] = $tag->id;
                return $attributes;
            }
        );
    }

    /**
     * Indicate taggable model.
     *
     * @param BaseModel $model
     *
     * @return static
     */
    public function withModel(BaseModel $model): static
    {
        return $this->state(
            function (array $attributes) use ($model) {
                $attributes['taggable_id'] = $model->id;
                $attributes['taggable_type'] = $model->type;
                return $attributes;
            }
        );
    }
}
