<?php

namespace App\Http\Filters;

use App\Models\BaseModel;
use App\Queries\Interfaces\CategorizableInterface;
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
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        if ($query instanceof CategorizableInterface) {
            $query->withAnyOfCategories(...$this->extract($value));
        }
    }

    /**
     * Extract categories' ids from given value.
     *
     * @param mixed $value
     *
     * @return array
     */
    protected function extract(mixed $value): array
    {
        if (is_int($value)) {
            return [$value];
        }

        if (is_string($value)) {
            return array_map(
                fn ($val) => (int) $val,
                explode(',', $value)
            );
        }

        if (is_array($value)) {
            return array_map(
                fn ($val) => (int) $val,
                $value
            );
        }

        return [];
    }
}
