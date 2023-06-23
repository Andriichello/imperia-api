<?php

namespace App\Queries;

use App\Models\Restaurant;
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
     * @param Restaurant|int ...$restaurants
     *
     * @return static
     */
    public function withRestaurant(Restaurant|int ...$restaurants): static
    {
        $this->join('restaurant_service as rs', 'rs.service_id', '=', 'services.id')
            ->whereIn('rs.restaurant_id', $this->extract('id', ...$restaurants))
            ->select('services.*');

        return $this;
    }
}
