<?php

namespace App\Http\Controllers\Other;

use App\Helpers\MetricsHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Metrics\FullReportRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Banquet;
use Closure;
use Illuminate\Support\Collection;

/**
 * Class MetricsController.
 */
class MetricsController extends Controller
{
    /**
     * Helper to use for calculating metrics.
     *
     * @var MetricsHelper
     */
    protected MetricsHelper $helper;

    /**
     * MetricsController constructor.
     *
     * @param MetricsHelper $helper
     */
    public function __construct(MetricsHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Get full metrics report for the specified timeframe.
     *
     * @param FullReportRequest $request
     *
     * @return ApiResponse
     */
    public function full(FullReportRequest $request): ApiResponse
    {
        $data = Banquet::query()
            ->withRestaurant($request->restaurant())
            ->thatAreRelevant()
            ->within($request->beg(), $request->end(), 'start_at')
            ->chunkMap(function (Banquet $banquet) use ($request) {
                $id = $banquet->id;
                $guests = $this->helper->guests($banquet);
                $totals = $this->helper->totals($banquet);
                $totals['metrics'] = $this->helper->metricsOnTotals($totals);
                $sales = $this->helper->sales($banquet);
                $sales['menus'] = $this->helper->salesByMenus($sales, $request->menus());

                return compact('id', 'guests', 'totals', 'sales');
            });

        $guests = $this->sumOfGuests($data->pluck('guests'));
        $totals = $this->sumOfTotals($data->pluck('totals'));
        $totals['metrics'] = $this->helper->metricsOnTotals($totals);
        $sales = $this->sumOfSales($data->pluck('sales'));

        $summary = compact('guests', 'totals', 'sales');

        return ApiResponse::make(compact('summary'));
    }

    /**
     * Calculate the sum of a column for all the given records.
     *
     * @param string $key
     * @param int|float|Closure $default
     * @param Collection $records
     *
     * @return int|float
     */
    protected function sumOf(string $key, int|float|Closure $default, Collection $records): int|float
    {
        return collect($records)
            ->sum(function ($record) use ($key, $default) {
                if ($default instanceof Closure) {
                    $default = $default($record);
                }

                return data_get($record, $key) ?? $default;
            });
    }

    /**
     * Returns the sum of all given guests metrics.
     *
     * @param Collection $records
     *
     * @return array
     */
    protected function sumOfGuests(Collection $records): array
    {
        return [
            'adults' => $this->sumOf('adults', 0, $records),
            'children' => $this->sumOf('children', 0, $records),
            'total' => $this->sumOf('total', 0, $records),
        ];
    }

    /**
     * Returns the sum of all given totals metrics.
     *
     * @param Collection $records
     *
     * @return array
     */
    protected function sumOfTotals(Collection $records): array
    {
        $closure = fn($record) => data_get($record, 'total', 0.0);

        return [
            'total' => $this->sumOf('total', 0.0, $records),
            'actual_total' => $this->sumOf('actual_total', $closure, $records),
            'tickets_total' => $this->sumOf('tickets_total', 0.0, $records),
        ];
    }

    /**
     * Returns the sum of all given sales metrics.
     *
     * @param Collection $records
     *
     * @return array
     */
    protected function sumOfSales(Collection $records): array
    {
        $summary = [
            'menus' => [],
            'products' => [],
        ];

        $sum = function ($id, $sale, $carry) {
            $accumulator = data_get($carry, $sale['id'], []);

            $accumulator['id'] = $sale['id'];
            $accumulator['amount'] = $sale['amount']
                + data_get($accumulator, 'amount', 0);
            $accumulator['total'] = $sale['total']
                + data_get($accumulator, 'total', 0.0);

            return $accumulator;
        };

        foreach ($records as $record) {
            foreach ($record['menus'] as $id => $sale) {
                $summary['menus'][$id] = $sum($id, $sale, $summary['menus']);
            }

            foreach ($record['products'] as $id => $sale) {
                $summary['products'][$id] = $sum($id, $sale, $summary['products']);
            }
        }

        $summary['menus'] = array_values($summary['menus']);
        $summary['products'] = array_values($summary['products']);

        return $summary;
    }
}
