<?php

namespace App\Queries\Traits;

use App\Models\Morphs\Tag;
use App\Queries\BaseQueryBuilder;

/**
 * Trait Taggable.
 *
 * @mixin BaseQueryBuilder
 */
trait Taggable
{
    /**
     * @param Tag|int ...$tags
     *
     * @return $this
     */
    public function withAllOfTags(Tag|int ...$tags): static
    {
        $this->havingTags(true, ...$tags);

        return $this;
    }

    /**
     * @param Tag|int ...$tags
     *
     * @return $this
     */
    public function withAnyOfTags(Tag|int ...$tags): static
    {
        $this->havingTags(false, ...$tags);

        return $this;
    }

    /**
     * @param bool $all
     * @param Tag|int ...$tags
     *
     * @return $this
     */
    private function havingTags(bool $all, Tag|int ...$tags): static
    {
        $model = $this->getModel();
        $table = $model->getTable();

        $ids = array_unique($this->extract('id', ...$tags));

        $sub = $model::query()
            ->select("{$table}.id")
            ->join('taggables', 'taggable_id', '=', "{$table}.id")
            ->where('taggable_type', slugClass($model))
            ->whereIn('tag_id', $ids)
            ->groupBy("{$table}.id");

        if ($all) {
            $sub->havingRaw('count(*) = ?', [count($ids)]);
        }

        $this->whereIn("{$table}.id", $sub);

        return $this;
    }
}
