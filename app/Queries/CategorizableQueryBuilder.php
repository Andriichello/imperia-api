<?php

namespace App\Queries;

use App\Models\Morphs\Category;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

/**
 * Trait CategorizableQueryBuilder.
 *
 * @mixin EloquentBuilder
 */
trait CategorizableQueryBuilder
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
