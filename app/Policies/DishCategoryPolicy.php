<?php

namespace App\Policies;

use App\Models\DishCategory;
use App\Policies\Base\RestaurantItemCrudPolicy;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DishCategoryPolicy.
 */
class DishCategoryPolicy extends RestaurantItemCrudPolicy
{
    /**
     * Get the model of the policy.
     *
     * @return Model|string
     */
    public function model(): Model|string
    {
        return DishCategory::class;
    }
}
