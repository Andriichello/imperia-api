<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder as ElBuilder;
use Spatie\QueryBuilder\Filters\Filter;

/**
 * Class MenusFilter.
 *
 * Filters items to those that have at least one of restaurant attached.
 *
 * @package App\Http\Filters
 */
class MenusFilter implements Filter
{
    use BaseFilterTrait;

    /**
     * Name of the pivot table.
     *
     * @var string
     */
    protected string $pivot = 'menu_product';

    /**
     * Name of the foreign key.
     *
     * @var string
     */
    protected string $foreignKey = 'product_id';

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
            ->whereIn("{$this->pivot}.menu_id", $this->extract($value));
    }
}
