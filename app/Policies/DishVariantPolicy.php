<?php

namespace App\Policies;

use App\Models\DishVariant;
use App\Policies\Base\RestaurantItemCrudPolicy;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DishVariantPolicy.
 */
class DishVariantPolicy extends RestaurantItemCrudPolicy
{
    /**
     * Get the model of the policy.
     *
     * @return Model|string
     */
    public function model(): Model|string
    {
        return DishVariant::class;
    }
}
