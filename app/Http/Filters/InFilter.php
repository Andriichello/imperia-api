<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

/**
 * Class InFilter.
 *
 * Filters items to those that have a column
 * equal to one of given values.
 *
 * @package App\Http\Filters
 */
class InFilter implements Filter
{
    use BaseFilterTrait;

    /**
     * The column to be filtered.
     *
     * @var string
     */
    protected string $column;

    /**
     * InFilter constructor.
     *
     * @param string $column
     */
    public function __construct(string $column = 'id')
    {
        $this->column = $column;
    }

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
        $values = is_string($value) ? explode(',', $value) : $value;

        $query->whereIn($this->column, $values);
    }
}
