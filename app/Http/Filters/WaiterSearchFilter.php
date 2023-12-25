<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

/**
 * Class WaiterSearchFilter.
 *
 * @package App\Http\Filters
 */
class WaiterSearchFilter implements Filter
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
        $query->where(function (Builder $q) use ($value) {
            $q->where('uuid', 'like', "%$value%")
                ->orWhere('name', 'like', "%$value%")
                ->orWhere('surname', 'like', "%$value%")
                ->orWhere('email', 'like', "%$value%")
                ->orWhere('phone', 'like', "%$value%");
        });
    }
}
