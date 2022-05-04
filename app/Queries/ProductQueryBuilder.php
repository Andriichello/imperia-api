<?php

namespace App\Queries;

use App\Models\Orders\Order;

/**
 * Class ProductQueryBuilder.
 */
class ProductQueryBuilder extends BaseQueryBuilder
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
