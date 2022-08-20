<?php

namespace App\Repositories;

use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Model;

/**
 * Class RestaurantRepository.
 */
class RestaurantRepository extends CrudRepository
{
    /**
     * Repository's target model class.
     *
     * @var Model|string
     */
    protected Model|string $model = Restaurant::class;
}
