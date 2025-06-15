<?php

namespace App\Repositories;

use App\Models\DishCategory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DishCategoryRepository.
 */
class DishCategoryRepository extends CrudRepository
{
    /**
     * Repository's target model class.
     *
     * @var Model|string
     */
    protected Model|string $model = DishCategory::class;
}
