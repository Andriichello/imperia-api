<?php

namespace App\Queries;

use App\Models\Menu;
use App\Queries\Interfaces\ArchivableInterface;
use App\Queries\Interfaces\CategorizableInterface;
use App\Queries\Traits\Archivable;
use App\Queries\Traits\Categorizable;

/**
 * Class ProductQueryBuilder.
 */
class ProductQueryBuilder extends BaseQueryBuilder implements
    ArchivableInterface,
    CategorizableInterface
{
    use Archivable;
    use Categorizable;

    /**
     * @param Menu|int $menu
     *
     * @return static
     */
    public function withMenu(Menu|int $menu): static
    {
        $menuId = is_int($menu) ? $menu : $menu->id;
        $this->where('products.menu_id', $menuId);

        return $this;
    }
}
