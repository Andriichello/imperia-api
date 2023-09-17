<?php

namespace App\Nova\Metrics;

use App\Models\Banquet;
use DateInterval;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;
use Laravel\Nova\Metrics\PartitionResult;
use Laravel\Nova\Metrics\Trend;
use Laravel\Nova\Metrics\TrendResult;
use Laravel\Nova\Util;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * BanquetsPerState class
 */
class BanquetsPerState extends Partition
{
    public function __construct($component = null)
    {
        parent::__construct($component);

        $this->name = __('nova.metrics.banquets_per_state');
    }
    /**
     * Calculate the value of the metric.
     *
     * @param NovaRequest $request
     *
     * @return PartitionResult
     */
    public function calculate(NovaRequest $request): PartitionResult
    {
        return $this->count($request, Banquet::class, 'state');
    }

    /**
     * Determine the amount of time the results of the metric should be cached.
     *
     * @return DateTimeInterface|DateInterval|float|int|null
     */
    public function cacheFor(): mixed
    {
        return null;
//         return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey(): string
    {
        return 'banquets-per-state';
    }

    /**
     * Format the aggregate result for the partition.
     *
     * @param  Model  $result
     * @param  string  $groupBy
     * @return array<string|int, int|float>
     */
    protected function formatAggregateResult($result, $groupBy): array
    {
        $result = parent::formatAggregateResult($result, $groupBy);

        $state = array_key_first($result);

        return [__("enum.state.$state") => $result[$state]];
    }
}
