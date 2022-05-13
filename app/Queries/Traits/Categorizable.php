<?php

namespace App\Queries\Traits;

use App\Models\Morphs\Category;
use App\Queries\BaseQueryBuilder;

/**
 * Trait Categorizable.
 *
 * @mixin BaseQueryBuilder
 */
trait Categorizable
{
    /**
     * @param Category ...$categories
     *
     * @return $this
     */
    public function withAllOfCategories(Category ...$categories): static
    {
        $ids = collect($categories)->pluck('id')->unique();
        $this->whereHas(
            'categories',
            fn ($query) => $query->whereIn('id', $ids->all()),
            '=',
            $ids->count()
        );

        return $this;
    }

    /**
     * @param Category ...$categories
     *
     * @return $this
     */
    public function withAnyOfCategories(Category ...$categories): static
    {
        $ids = collect($categories)->pluck('id')->unique();
        $this->whereHas(
            'categories',
            fn ($query) => $query->whereIn('id', $ids->all())
        );

        return $this;
    }
}
