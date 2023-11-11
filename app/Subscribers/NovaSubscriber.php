<?php

namespace App\Subscribers;

use App\Models\Banquet;
use App\Models\Orders\Order;

/**
 * Class NovaSubscriber.
 */
class NovaSubscriber extends BaseSubscriber
{
    protected function map(): void
    {
        $this->map = [
            Banquet::eloquentEvent('created') => 'banquetCreated',
        ];
    }

    public function banquetCreated(Banquet $banquet)
    {
        $order = new Order();
        $order->save();

        $banquet->orders()->attach($order->id);
    }
}
