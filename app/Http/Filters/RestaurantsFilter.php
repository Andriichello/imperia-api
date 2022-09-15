<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder as ElBuilder;
use Spatie\QueryBuilder\Filters\Filter;

/**
 * Class RestaurantsFilter.
 *
 * Filters items to those that have one of restaurant attached.
 *
 * @package App\Http\Filters
 */
class RestaurantsFilter implements Filter
{
    use BaseFilterTrait;

    /**
     * Name of the pivot table.
     *
     * @var string
     */
    protected string $pivot;

    /**
     * Name of the foreign key.
     *
     * @var string
     */
    protected string $foreignKey;

    /**
     * RestaurantsFilter constructor.
     *
     * @param string $pivot
     * @param string $foreignKey
     */
    public function __construct(string $pivot, string $foreignKey)
    {
        $this->pivot = $pivot;
        $this->foreignKey = $foreignKey;
    }

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

        $first = "{$this->pivot}.{$this->foreignKey}";
        $second = "{$table}.id";

        $query->join($this->pivot, $first, '=', $second)
            ->whereIn("{$this->pivot}.restaurant_id", $this->extract($value));
    }
}
