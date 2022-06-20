<?php

namespace App\Queries;

use App\Models\Orders\Order;
use App\Queries\Interfaces\ArchivableInterface;
use App\Queries\Interfaces\CategorizableInterface;
use App\Queries\Traits\Archivable;
use App\Queries\Traits\Categorizable;

/**
 * Class ServiceQueryBuilder.
 */
class ServiceQueryBuilder extends BaseQueryBuilder implements
    ArchivableInterface,
    CategorizableInterface
{
    use Archivable;
    use Categorizable;

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
