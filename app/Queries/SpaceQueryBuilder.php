<?php

namespace App\Queries;

use App\Models\Restaurant;
use App\Models\Space;
use App\Queries\Interfaces\ArchivableInterface;
use App\Queries\Interfaces\CategorizableInterface;
use App\Queries\Traits\Archivable;
use App\Queries\Traits\Categorizable;

/**
 * Class SpaceQueryBuilder.
 *
 * @method Space|null first($columns = ['*'])
 * @method Space|null firstOrFail($columns = ['*'])
 * @method Space|null find($columns = ['*'])
 * @method Space|null findOrFail($id, $columns = ['*'])
 * @method $this where($column, $operator = null, $value = null, $boolean = 'and')
 * @method $this orWhere($column, $operator = null, $value = null)
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
        $ids = $this->extract('id', ...$restaurants);

        if (!empty($ids)) {
            $this->whereIn($this->model->getTable() . '.restaurant_id', $ids);
        }

        return $this;
    }
}
