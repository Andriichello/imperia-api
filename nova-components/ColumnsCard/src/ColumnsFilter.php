<?php

namespace Andriichello\ColumnsCard;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

/**
 * Class ColumnsFilter.
 */
class ColumnsFilter extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'mocked-select-filter';

    /**
     * Apply the filter to the given query.
     *
     * @param Request $request
     * @param Builder $query
     * @param mixed $value
     *
     * @return Builder
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply(Request $request, $query, $value): Builder
    {
        return $query;
    }

    /**
     * Get the filter's available options.
     *
     * @param Request $request
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function options(Request $request): array
    {
        return [];
    }
}
