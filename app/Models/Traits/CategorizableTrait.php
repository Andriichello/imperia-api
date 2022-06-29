<?php

namespace App\Models\Traits;

use App\Models\BaseModel;
use App\Models\Morphs\Categorizable;
use App\Models\Morphs\Category;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * Trait CategorizableTrait.
 *
 * @mixin BaseModel
 *
 * @property Category[]|Collection $categories
 */
trait CategorizableTrait
{
    /**
     * Categories related to the model.
     *
     * @return MorphToMany
     */
    public function categories(): MorphToMany
    {
        return $this->morphToMany(
            Category::class, // related model
            'categorizable', // morph relation name
            Categorizable::class, // morph relation table
            'categorizable_id', // morph table pivot key to current model
            'category_id' // morph table pivot key to related model
        );
    }

    /**
     * Attach given categories to the model.
     *
     * @param Category ...$categories
     *
     * @return void
     */
    public function attachCategories(Category ...$categories): void
    {
        $this->categories()->attach(extractValues('id', ...$categories));
    }

    /**
     * Detach given categories from the model.
     *
     * @param Category ...$categories
     *
     * @return void
     */
    public function detachCategories(Category ...$categories): void
    {
        $this->categories()->detach(extractValues('id', ...$categories));
    }

    /**
     * Determines if model has categories attached.
     *
     * @return bool
     */
    public function hasCategories(): bool
    {
        return $this->categories()->exists();
    }

    /**
     * Determines if model has all categories attached.
     *
     * @param Category ...$categories
     *
     * @return bool
     */
    public function hasAllOfCategories(Category ...$categories): bool
    {
        $ids = array_map(fn(Category $category) => $category->id, $categories);
        $count = $this->categories()->whereIn('id', $ids)->count();
        return count($categories) === $count;
    }

    /**
     * Determines if model has any of categories attached.
     *
     * @param Category ...$categories
     *
     * @return bool
     */
    public function hasAnyOfCategories(Category ...$categories): bool
    {
        $ids = array_map(fn(Category $category) => $category->id, $categories);
        return empty($ids) || $this->categories()->whereIn('id', $ids)->exists();
    }
}
