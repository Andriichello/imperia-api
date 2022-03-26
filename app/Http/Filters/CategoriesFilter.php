<?php

namespace App\Http\Filters;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Spatie\QueryBuilder\Filters\Filter;

/**
 * Class CategoriesFilter.
 *
 * Filters items to those that have at least one of the
 * specified categories attached to them.
 *
 * @package App\Http\Filters
 */
class CategoriesFilter implements Filter
{
    /**
     * @param Builder $query
     * @param mixed $value
     * @param string $property
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Builder $query, mixed $value, string $property)
    {
        /** @var BaseModel $model */
        $model = $query->getModel();

        $query->join(
            'categorizables',
            function (JoinClause $join) use ($value, $model) {
                $join->on('categorizables.categorizable_id', '=', $model->getTable() . '.id')
                    ->where('categorizables.categorizable_type', '=', $model->getTypeAttribute())
                    ->whereIn('categorizables.category_id', Arr::wrap($value));
            }
        )->select($model->getTable() . '.*');
    }
}
