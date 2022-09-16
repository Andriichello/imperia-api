<?php

namespace App\Queries;

use App\Models\Restaurant;
use App\Queries\Interfaces\ArchivableInterface;
use App\Queries\Interfaces\CategorizableInterface;
use App\Queries\Traits\Archivable;
use App\Queries\Traits\Categorizable;

/**
 * Class SpaceQueryBuilder.
 */
class SpaceQueryBuilder extends BaseQueryBuilder implements
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
        $this->join('restaurant_space.space_id', '=', 'spaces.id')
            ->whereIn('restaurant_space.restaurant_id', $this->extract('id', $restaurants))
            ->select('spaces.*');

        return $this;
    }
}
