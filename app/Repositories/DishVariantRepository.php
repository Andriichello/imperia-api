<?php

namespace App\Repositories;

use App\Models\DishVariant;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DishVariantRepository.
 */
class DishVariantRepository extends CrudRepository
{
    /**
     * Repository's target model class.
     *
     * @var Model|string
     */
    protected Model|string $model = DishVariant::class;
}
