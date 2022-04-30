<?php

namespace App\Jobs;

use App\Models\Orders\Order;

/**
 * Class CalculateTotals.
 */
class StoreTotals extends BaseJob
{
    protected Order $order;

    /**
     * CalculateTotals constructor.
     *
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $banquet = $this->order->banquet;
        if ($banquet->canBeEdited()) {
            $banquet->setToJson('metadata', 'totals', $this->order->totals);
            $banquet->save();
        }
    }
}
