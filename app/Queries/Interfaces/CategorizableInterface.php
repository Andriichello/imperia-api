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
     * @return BaseQueryBuilder
     */
    public function withAllOfCategories(Category ...$categories): BaseQueryBuilder;

    /**
     * @param Category ...$categories
     *
     * @return BaseQueryBuilder
     */
    public function withAnyOfCategories(Category ...$categories): BaseQueryBuilder;
}
