<?php

namespace App\Queries;

use App\Models\Orders\Order;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

/**
 * Class TicketQueryBuilder.
 */
class TicketQueryBuilder extends EloquentBuilder
{
    use CategorizableQueryBuilder;

    /**
     * @param Order|int $order
     *
     * @return static
     */
    public function withOrder(Order|int $order): static
    {
        $orderId = is_int($order) ? $order : $order->id;
        $this->where('order_id', $orderId);

        return $this;
    }
}
