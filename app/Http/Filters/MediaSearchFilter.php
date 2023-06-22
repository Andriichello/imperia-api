<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

/**
 * Class MediaSearchFilter.
 *
 * @package App\Http\Filters
 */
class MediaSearchFilter implements Filter
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
            $q->where('name', 'like', "%$value%")
                ->orWhere('title', 'like', "%$value%")
                ->orWhere('extension', 'like', "%$value%");
        });
    }
}
