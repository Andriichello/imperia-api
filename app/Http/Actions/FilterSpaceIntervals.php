<?php

namespace App\Http\Actions;

use App\Http\Controllers\Traits\Filterable;
use App\Models\Space;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Collection;

class FilterSpaceIntervals
{
    protected LoadSpaceIntervals $loadIntervals;

    protected array $appliedFilters = [];

    /**
     * @return array
     */
    public function getAppliedFilters(): array
    {
        return $this->appliedFilters;
    }

    /**
     * FilterSpaceIntervals constructor.
     *
     * @param LoadSpaceIntervals $loadIntervals
     */
    public function __construct(LoadSpaceIntervals $loadIntervals)
    {
        $this->loadIntervals = $loadIntervals;
    }

    /**
     * Loads Space's business intervals for the specified period of time.
     *
     * @param Space $instance
     * @param array $filters
     * @return Collection
     */
    public function execute(Space $instance, array $filters = []): Collection
    {
        if (empty($instance) || empty($filters)) {
            return new Collection();
        }

        $begDatetimeFilter = Filterable::extractFilter($filters, 'beg_datetime');
        $endDatetimeFilter = Filterable::extractFilter($filters, 'end_datetime');
        $intervals = $this->loadIntervals->execute(
            $instance,
            Filterable::extractFilterValue($begDatetimeFilter, Carbon::now()->format('Y-m-d H:i:s')),
            Filterable::extractFilterValue($endDatetimeFilter)
        );
        $this->appliedFilters = $this->loadIntervals->getAppliedFilters();

        $banquetIdFilter = Filterable::extractFilter($filters, 'banquet_id');
        if (!empty($banquetIdFilter)) {
            $intervals = $intervals->filter(function ($interval) use ($banquetIdFilter) {
                return Filterable::isMatchingWhereConditions($interval, [$banquetIdFilter], false);
            });

            $this->appliedFilters[] = $banquetIdFilter;
        }
        return $intervals;
    }
}
