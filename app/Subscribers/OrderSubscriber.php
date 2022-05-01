<?php

namespace App\Subscribers;

use App\Models\Orders\Order;

/**
 * Class OrderSubscriber.
 */
class OrderSubscriber extends BaseSubscriber
{
    protected function map(): void
    {
        $this->map = [
            Order::eloquentEvent('deleted') => 'deleted',
        ];
    }

    public function deleted(Order $order)
    {
        if (empty($order->banquet)) {
            return;
        }

        $order->banquet->totals = null;
        $order->banquet->save();
    }
}
