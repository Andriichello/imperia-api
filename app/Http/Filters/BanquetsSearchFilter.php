<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

/**
 * Class BanquetsSearchFilter.
 *
 * @package App\Http\Filters
 */
class BanquetsSearchFilter implements Filter
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
            $q->where('title', 'like', "%$value%")
                ->orWhereHas('customer', function (Builder $c) use ($value) {
                    $filter = new CustomersSearchFilter();
                    $filter($c, $value, 'search');
                });
        });
    }
}
