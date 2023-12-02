<?php

namespace App\Helpers;

use App\Helpers\Interfaces\MetricsHelperInterface;
use App\Models\Banquet;
use App\Models\Menu;
use App\Models\Orders\ProductOrderField;

/**
 * Class MetricsHelper.
 */
class MetricsHelper implements MetricsHelperInterface
{
    /**
     * Get number of guests for the given banquet.
     *
     * @param Banquet $banquet
     *
     * @return array{
     *    adults: integer,
     *    children: integer,
     *    total: integer
     * }
     */
    public function guests(Banquet $banquet): array
    {
        $adults = (int) $banquet->adults_amount;
        $children = (int) $banquet->children_amount;
        $total = $adults + $children;

        return compact('adults', 'children', 'total');
    }

    /**
     * Get totals for the banquet.
     *
     * @param Banquet $banquet
     *
     * @return array{
     *   total: float,
     *   actual_total: float|null,
     *   tickets_total: float
     * }
     */
    public function totals(Banquet $banquet): array
    {
        // tickets, which are set to metadata are not included
        $total = (float) data_get($banquet->totals, 'all');

        return [
            'total' => $total + $banquet->tickets_total,
            'actual_total' => $banquet->actual_total,
            'tickets_total' => $banquet->tickets_total,
        ];
    }

    /**
     * Get metrics for the given totals.
     *
     * @param array{
     *   total: float,
     *   actual_total: float|null,
     *   tickets_total: float
     * } $totals
     *
     * @return array{
     *   difference: float,
     *   percentage: float|null
     * }
     */
    public function metricsOnTotals(array $totals): array
    {
        $full = data_get($totals, 'total');
        $actual = data_get($totals, 'actual_total');

        return [
            'difference' => $diff = (($actual ?? $full) - $full),
            'percentage' => $full !== 0.0
                ? round(($diff / $full) * 100, 3) : null,
        ];
    }

    /**
     * Get sales information for the given banquet.
     * It includes:
     *  - sum of sales per product
     *  - amount of sales per product
     *
     * @param Banquet $banquet
     *
     * @return array{
     *   products: array<int, array{
     *     id: int,
     *     amount: int,
     *     total: float
     *   }>
     * }
     */
    public function sales(Banquet $banquet): array
    {
        $products = [];

        /* @phpstan-ignore-next-line */
        $banquet->order->products()
            ->each(function (ProductOrderField $field) use (&$products) {
                $info = data_get($products, $field->product_id, []);

                $info['id'] = $field->product_id;
                $info['amount'] = data_get($info, 'amount', 0) + $field->amount;
                $info['total'] = data_get($info, 'total', 0.0) + $field->total;

                $products[$field->product_id] = $info;
            });

        return compact('products');
    }

    /**
     * Get sales by menus from the given sales information.
     * It includes:
     *  - sum of sales per menu
     *  - amount of sales per menu
     *
     * @param array{
     *   products: array<int, array{
     *     id: int,
     *     amount: int,
     *     total: float
     *   }>
     * } $sales
     * @param array|Menu[] $menus
     *
     * @return array
     */
    public function salesByMenus(array $sales, array $menus): array
    {
        $salesByMenus = [];

        foreach ($menus as $menu) {
            $saleByMenu = [
                'id' => $menu->id,
                'amount' => 0,
                'total' => 0.0,
            ];

            /* @phpstan-ignore-next-line */
            $ids = $menu->products()
                ->pluck('id')
                ->all();

            foreach ($sales['products'] as $sale) {
                if (!in_array($sale['id'], $ids)) {
                    continue;
                }

                $saleByMenu['amount'] += $sale['amount'];
                $saleByMenu['total'] += $sale['total'];
            }

            $salesByMenus[$menu->id] = $saleByMenu;
        }

        return $salesByMenus;
    }
}
