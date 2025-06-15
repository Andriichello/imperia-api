<?php

namespace App\Repositories;

use App\Models\Dish;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DishRepository.
 */
class DishRepository extends CrudRepository
{
    /**
     * Repository's target model class.
     *
     * @var Model|string
     */
    protected Model|string $model = Dish::class;
}
