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
            Order::eloquentEvent('saved') => 'saved',
        ];
    }

    public function saved(Order $order)
    {
        //
    }
}
