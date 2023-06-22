<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder as ElBuilder;
use Spatie\QueryBuilder\Filters\Filter;

/**
 * Class MockFilter.
 *
 * @package App\Http\Filters
 */
class MockFilter implements Filter
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
        //
    }
}
