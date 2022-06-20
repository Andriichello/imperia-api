<?php

namespace App\Queries\Interfaces;

use App\Models\Morphs\Category;
use App\Queries\BaseQueryBuilder;

/**
 * Interface CategorizableInterface.
 */
interface CategorizableInterface
{
    /**
     * @param Category ...$categories
     *
     * @return static
     */
    public function withAllOfCategories(Category ...$categories): static;

    /**
     * @param Category ...$categories
     *
     * @return static
     */
    public function withAnyOfCategories(Category ...$categories): static;
}
