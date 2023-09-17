<?php

namespace App\Nova\Metrics;

use App\Models\Banquet;
use DateInterval;
use DateTimeInterface;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Trend;
use Laravel\Nova\Metrics\TrendResult;

/**
 * BanquetTotalsPerDay class
 */
class BanquetTotalsPerDay extends Trend
{
    public function __construct($component = null)
    {
        parent::__construct($component);

        $this->name = __('nova.metrics.banquets_total');
    }

    /**
     * Calculate the value of the metric.
     *
     * @param NovaRequest $request
     *
     * @return TrendResult
     */
    public function calculate(NovaRequest $request): TrendResult
    {
        $trend = [];

        $days = $request->get('range');

        if ($days > 0) {
            $start = now()->setTime(0, 0);
            $end = now()->addDays($days)->setTime(23, 59);
        }

        if ($days <= 0) {
            $start = now()->subDays(-$days)->setTime(0, 0);
            $end = now();
        }

        $date = $start->clone();
        while ($date->isBefore($end)) {
            $formatted = $this->formatAggregateResultDate(
                $date->toDateString(),
                'day',
                false
            );

            $trend[$formatted] = 0;
            $date = $date->addDay();
        }

        Banquet::query()
            ->index($request->user())
            ->where('banquets.start_at', '>=', $start->toDateString())
            ->where('banquets.start_at', '<=', $end->toDateString())
            ->each(function (Banquet $banquet) use (&$trend) {
                $formatted = $this->formatAggregateResultDate(
                    $banquet->start_at->toDateString(),
                    'day',
                    false
                );


                $trend[$formatted] += data_get($banquet, 'totals.all');
            });

        return $this->result(array_sum($trend))
            ->trend($trend);
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges(): array
    {
        return [
            -7 => '-' . __('nova.metrics.days.7'),
            -14 => '-' . __('nova.metrics.days.14'),
            -21 => '-' . __('nova.metrics.days.21'),
            -30 => '-' . __('nova.metrics.days.30'),
            -60 => '-' . __('nova.metrics.days.60'),
            -90 => '-' . __('nova.metrics.days.90'),
            7 => __('nova.metrics.days.7'),
            14 => __('nova.metrics.days.14'),
            21 => __('nova.metrics.days.21'),
            30 => __('nova.metrics.days.30'),
            60 => __('nova.metrics.days.60'),
            90 => __('nova.metrics.days.90'),
        ];
    }

    /**
     * Determine the amount of time the results of the metric should be cached.
     *
     * @return DateTimeInterface|DateInterval|float|int|null
     */
    public function cacheFor(): mixed
    {
        return null;
         // return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey(): string
    {
        return 'banquet-totals-per-past-day';
    }
}
