<?php

namespace App\Models\Interfaces;

use App\Models\Morphs\Category;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;

/**
 * Interface CategorizableInterface.
 *
 * @property Category[]|Collection $categories
 */
interface CategorizableInterface
{
    /**
     * Categories related to the model.
     *
     * @return MorphToMany
     */
    public function categories(): MorphToMany;

    /**
     * Attach given categories to the model.
     *
     * @param Category ...$categories
     *
     * @return void
     */
    public function attachCategories(Category ...$categories): void;

    /**
     * Detach given categories from the model.
     *
     * @param Category ...$categories
     *
     * @return void
     */
    public function detachCategories(Category ...$categories): void;

    /**
     * Determines if model has categories attached.
     *
     * @return bool
     */
    public function hasCategories(): bool;

    /**
     * Determines if model has all categories attached.
     *
     * @param Category ...$categories
     *
     * @return bool
     */
    public function hasAllOfCategories(Category ...$categories): bool;

    /**
     * Determines if model has any of categories attached.
     *
     * @param Category ...$categories
     *
     * @return bool
     */
    public function hasAnyOfCategories(Category ...$categories): bool;
}
