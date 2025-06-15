<?php

namespace App\Policies;

use App\Models\DishMenu;
use App\Policies\Base\RestaurantItemCrudPolicy;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DishMenuPolicy.
 */
class DishMenuPolicy extends RestaurantItemCrudPolicy
{
    /**
     * Get the model of the policy.
     *
     * @return Model|string
     */
    public function model(): Model|string
    {
        return DishMenu::class;
    }
}
