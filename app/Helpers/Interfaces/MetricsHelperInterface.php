<?php

namespace App\Helpers\Interfaces;

use App\Models\Banquet;

/**
 * Interface MetricsHelperInterface.
 */
interface MetricsHelperInterface
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
    public function guests(Banquet $banquet): array;

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
    public function totals(Banquet $banquet): array;

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
    public function metricsOnTotals(array $totals): array;

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
    public function sales(Banquet $banquet): array;
}
