<?php

namespace App\Queries;

use App\Models\Restaurant;
use App\Models\Ticket;
use App\Queries\Interfaces\ArchivableInterface;
use App\Queries\Interfaces\CategorizableInterface;
use App\Queries\Traits\Archivable;
use App\Queries\Traits\Categorizable;

/**
 * Class TicketQueryBuilder.
 *
 * @method Ticket|null first($columns = ['*'])
 * @method Ticket|null firstOrFail($columns = ['*'])
 * @method Ticket|null find($columns = ['*'])
 * @method Ticket|null findOrFail($id, $columns = ['*'])
 * @method $this where($column, $operator = null, $value = null, $boolean = 'and')
 * @method $this orWhere($column, $operator = null, $value = null)
 */
class TicketQueryBuilder extends BaseQueryBuilder implements
    ArchivableInterface,
    CategorizableInterface
{
    use Archivable;
    use Categorizable;

    /**
     * @param Restaurant|int ...$restaurants
     *
     * @return static
     */
    public function withRestaurant(Restaurant|int ...$restaurants): static
    {
        $ids = $this->extract('id', ...$restaurants);

        if (!empty($ids)) {
            $this->whereIn($this->model->getTable() . '.restaurant_id', $ids);
        }

        return $this;
    }
}
