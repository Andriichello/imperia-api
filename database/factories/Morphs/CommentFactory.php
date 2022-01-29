<?php

namespace Database\Factories\Morphs;

use App\Models\BaseModel;
use App\Models\Morphs\Category;
use App\Models\Morphs\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class CategoryFactory.
 *
 * @method Comment|Collection create($attributes = [], ?Model $parent = null)
 */
class CommentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = Comment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'text' => $this->faker->unique()->sentence,
        ];
    }

    /**
     * Indicate comment text.
     *
     * @param string $text
     *
     * @return static
     */
    public function withText(string $text): static
    {
        return $this->state(
            function (array $attributes) use ($text) {
                $attributes['text'] = $text;
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
                $attributes['commentable_id'] = $model->id;
                $attributes['commentable_type'] = $model->type;
                return $attributes;
            }
        );
    }
}
