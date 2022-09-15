<?php

namespace App\Http\Filters;

use App\Queries\Interfaces\CategorizableInterface;
use Illuminate\Database\Eloquent\Builder;
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
    use BaseFilterTrait;

    /**
     * @param Builder $query
     * @param mixed $value
     * @param string $property
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        if ($query instanceof CategorizableInterface) {
            $query->withAnyOfCategories(...$this->extract($value));
        }
    }
}
