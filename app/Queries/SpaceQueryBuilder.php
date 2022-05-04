<?php

namespace App\Queries;

use App\Models\Orders\Order;
use App\Models\Space;
use DateTimeInterface;
use Illuminate\Database\Query\Builder as DatabaseBuilder;

/**
 * Class SpaceQueryBuilder.
 */
class SpaceQueryBuilder extends BaseQueryBuilder
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

    /**
     * @param Space|int $space
     *
     * @return $this
     */
    public function withSpace(Space|int $space): static
    {
        $spaceId = is_int($space) ? $space : $space->id;
        $this->where('space_id', $spaceId);

        return $this;
    }

    /**
     * @param DateTimeInterface $beg
     * @param DateTimeInterface $end
     *
     * @return $this
     */
    public function between(DateTimeInterface $beg, DateTimeInterface $end): static
    {
        $this->join('orders', 'orders.id', '=', 'space_order_fields.order_id')
            ->join('banquets', 'banquets.id', '=', 'orders.banquet_id')
            ->whereNested(function (DatabaseBuilder $query) use ($beg, $end) {
                $query->whereBetween('banquets.start_at', [$beg, $end])
                    ->orWhereBetween('banquets.end_at', [$beg, $end]);
            })
            ->select('space_order_fields.*');

        return $this;
    }
}
