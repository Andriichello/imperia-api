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
     * @param Category|int ...$categories
     *
     * @return $this
     */
    public function withAllOfCategories(Category|int ...$categories): static
    {
        $this->havingCategories(true, ...$categories);

        return $this;
    }

    /**
     * @param Category|int ...$categories
     *
     * @return $this
     */
    public function withAnyOfCategories(Category|int ...$categories): static
    {
        $this->havingCategories(false, ...$categories);

        return $this;
    }

    /**
     * @param bool $all
     * @param Category|int ...$categories
     *
     * @return $this
     */
    private function havingCategories(bool $all, Category|int ...$categories): static
    {
        $model = $this->getModel();
        $table = $model->getTable();

        $ids = array_unique($this->extract('id', ...$categories));

        $sub = $model::query()
            ->select("{$table}.id")
            ->join('categorizables', 'categorizable_id', '=', "{$table}.id")
            ->where('categorizable_type', slugClass($model))
            ->whereIn('category_id', $ids)
            ->groupBy("{$table}.id");

        if ($all) {
            $sub->havingRaw('count(*) = ?', [count($ids)]);
        }

        $this->whereIn("{$table}.id", $sub);

        return $this;
    }
}
