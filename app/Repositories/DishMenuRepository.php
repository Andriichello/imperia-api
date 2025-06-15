<?php

namespace App\Repositories;

use App\Models\DishMenu;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DishMenuRepository.
 */
class DishMenuRepository extends CrudRepository
{
    /**
     * Repository's target model class.
     *
     * @var Model|string
     */
    protected Model|string $model = DishMenu::class;
}
