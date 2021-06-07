<?php

namespace App\Http\Controllers\Implementations;

use App\Http\Controllers\DynamicController;
use App\Http\Requests\Implementations\SpaceRequest;
use App\Models\Space;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SpaceController extends DynamicController
{
    /**
     * Controller's model class name.
     *
     * @var ?string
     */
    protected ?string $model = Space::class;

    protected array $additionalFilters = ['banquet_id', 'beg_datetime', 'end_datetime'];

    public function __construct(SpaceRequest $request)
    {
        parent::__construct($request);
    }

    protected function applyAdditionalFilters(Builder|Collection $data, array $filters): Collection|Builder
    {
        if ($data instanceof Builder) {
            return $data;
        }

        $banquetFilters = $this->limitFilters($filters, ['banquet_id']);
        $intervalFilters = $this->limitFilters($filters, ['beg_datetime', 'end_datetime']);

        if (empty($data) || empty($intervalFilters)) {
            return $data;
        }

        $begDatetime = $endDatetime = null;
        foreach ($intervalFilters as $intervalFilter) {
            if ($intervalFilter[0] === 'beg_datetime') {
                $begDatetime = $intervalFilter[2];
            } else if ($intervalFilter[0] === 'end_datetime') {
                $endDatetime = $intervalFilter[2];
            }
        }

        foreach ($data as $instance) {
            $intervals = $this->loadIntervals($instance, $begDatetime = Carbon::now()->format('Y:m:d H:i:s'), $endDatetime);
            if (!empty($banquetFilters)) {
                $intervals = $intervals->filter(function ($interval) use ($banquetFilters) {
                    return $this->isMatchingWhereConditions($interval, $banquetFilters, false);
                });
            }

            $instance->intervals = $intervals;
        }

        // appending banquet filters to applied
        $this->appliedFilters = array_merge(
            $this->appliedFilters,
            $banquetFilters
        );

        // appending interval filters to applied
        if (isset($begDatetime) && isset($endDatetime)) {
            $this->appliedFilters[] = [
                ['beg_datetime', 'between', [$begDatetime, $endDatetime]],
                'or',
                ['end_datetime', 'between', [$begDatetime, $endDatetime]]
            ];
        } else if (isset($begDatetime)) {
            $this->appliedFilters[] = ['beg_datetime', '>=', $begDatetime];
        }

        return $data;
    }

    /**
     * Loads Space's business intervals for the specified period of time.
     *
     * @param Space $item
     * @param mixed $begDatetime
     * @param mixed $endDatetime
     * @return Collection
     */
    protected function loadIntervals(Space $item, mixed $begDatetime, mixed $endDatetime): Collection
    {
        if (!($item instanceof Space) || (empty($begDatetime) && empty($endDatetime))) {
            return new Collection();
        }

        $builder = $item->intervals();
        if (isset($begDatetime) && isset($endDatetime)) {
            // captures all intervals that intersect the specified interval
            $builder->where(function ($builder) use ($begDatetime, $endDatetime) {
                $builder->where(function ($builder) use ($begDatetime, $endDatetime) {
                    // starts between/on beginning and end
                    $builder->where('beg_datetime', '>=', $begDatetime) // starts after/on beginning
                    ->where('beg_datetime', '<=', $endDatetime); // starts before/on ending
                })->orWhere(function ($builder) use ($begDatetime, $endDatetime) {
                    // ends between/on beginning and end
                    $builder->where('end_datetime', '>=', $begDatetime) // ends after/on beginning
                    ->where('end_datetime', '<=', $endDatetime); // ends before/on ending
                })->orWhere(function ($builder) use ($begDatetime, $endDatetime) {
                    // starts before/on beginning and ends after/on ending
                    $builder->where('beg_datetime', '<=', $begDatetime) // starts before/on beginning
                    ->where('end_datetime', '>=', $endDatetime); // ends after/on ending
                });
            });
        } else if (isset($begDatetime)) {
            // captures all intervals starting from specified datetime
            $builder->where(function ($builder) use ($begDatetime) {
                $builder->where(function ($builder) use ($begDatetime) {
                    $builder->where('end_datetime', '>=', $begDatetime) // ends after/on beginning
                    ->where('beg_datetime', '<=', $begDatetime); // begins before/on beginning
                })->orWhere('beg_datetime', '>=', $begDatetime); // begins after/on beginning
            });
        }

        $intervals = $builder->get();
        foreach ($intervals as $interval) {
            $interval->makeHidden('banquet');
            $interval->banquet_id = $interval->banquet->banquet_id ?? null;
        }

        return $intervals;
    }
}
