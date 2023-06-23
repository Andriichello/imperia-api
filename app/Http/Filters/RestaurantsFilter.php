<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder as ElBuilder;
use Spatie\QueryBuilder\Filters\Filter;

/**
 * Class RestaurantsFilter.
 *
 * Filters items to those that have one of restaurants attached.
 *
 * @package App\Http\Filters
 */
class RestaurantsFilter implements Filter
{
    use BaseFilterTrait;

    /**
     * @param ElBuilder $query
     * @param mixed $value
     * @param string $property
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ElBuilder $query, mixed $value, string $property): void
    {
        $table = $query->getModel()->getTable();

        $query->whereIn("$table.restaurant_id", $this->extract($value));
    }
}
