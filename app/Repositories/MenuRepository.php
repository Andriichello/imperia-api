<?php

namespace App\Repositories;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MenuRepository.
 */
class MenuRepository extends CrudRepository
{
    /**
     * Repository's target model class.
     *
     * @var Model|string
     */
    protected Model|string $model = Menu::class;
}
