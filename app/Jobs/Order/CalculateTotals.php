<?php

namespace App\Jobs\Order;

use App\Jobs\AsyncJob;
use App\Models\Orders\Order;

/**
 * Class CalculateTotals.
 */
class CalculateTotals extends AsyncJob
{
    /**
     * @var Order
     */
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
        $this->order->totals = $this->order->calculateTotals();
        $this->order->save();

        if ($this->order->banquet) {
            $this->order->banquet->totals = $this->order->totals;
            $this->order->banquet->save();
        }
    }
}
