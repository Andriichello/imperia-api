<?php

namespace App\Filament\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Trait ReordersByPopularity.
 *
 * Drag-and-drop ordering for list pages of records, which the website lists
 * by `popularity`, highest first. The page's table must be `->reorderable('popularity')`.
 */
trait ReordersByPopularity
{
    /**
     * While reordering, sort the records the same way as the website does.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    protected function applySortingToTableQuery(Builder $query): Builder
    {
        if (!$this->isTableReordering()) {
            return parent::applySortingToTableQuery($query);
        }

        $query->orderByDesc($query->qualifyColumn('popularity'))
            ->orderBy($query->getModel()->getQualifiedKeyName());

        return $query;
    }

    /**
     * Save the new order, giving the first record the highest popularity.
     *
     * Filament's own implementation numbers the records from the top,
     * and its update fails on queries with joins (restaurant scoping).
     *
     * @param array $order Keys of the records, from the top.
     *
     * @return void
     */
    public function reorderTable(array $order): void
    {
        if (!$this->getTable()->isReorderable()) {
            return;
        }

        $query = $this->getTable()->getQuery();
        $model = $query->getModel();

        // only records the user can see
        $keys = $query->whereIn($model->getQualifiedKeyName(), $order)
            ->pluck($model->getQualifiedKeyName())
            ->all();

        DB::transaction(function () use ($order, $keys, $model) {
            $popularity = count($order);

            foreach ($order as $key) {
                if (in_array($key, $keys)) {
                    $model->newQueryWithoutScopes()
                        ->whereKey($key)
                        ->update(['popularity' => $popularity]);
                }

                $popularity--;
            }
        });
    }
}
