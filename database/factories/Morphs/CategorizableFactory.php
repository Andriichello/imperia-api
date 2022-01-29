<?php

namespace Database\Factories\Morphs;

use App\Models\BaseModel;
use App\Models\Morphs\Categorizable;
use App\Models\Morphs\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class CategorizableFactory.
 *
 * @method Categorizable|Collection create($attributes = [], ?Model $parent = null)
 */
class CategorizableFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string|null
     */
    protected $model = Categorizable::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'category_id' => Category::factory()
        ];
    }

    /**
     * Indicate category.
     *
     * @param Category $category
     *
     * @return static
     */
    public function withCategory(Category $category): static
    {
        return $this->state(
            function (array $attributes) use ($category) {
                $attributes['category_id'] = $category->id;
                return $attributes;
            }
        );
    }

    /**
     * Indicate categorizable model.
     *
     * @param BaseModel $model
     *
     * @return static
     */
    public function withModel(BaseModel $model): static
    {
        return $this->state(
            function (array $attributes) use ($model) {
                $attributes['categorizable_id'] = $model->id;
                $attributes['categorizable_type'] = $model->type;
                return $attributes;
            }
        );
    }
}
