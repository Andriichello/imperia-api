<?php

namespace App\Policies;

use App\Models\MenuVersionChange;
use App\Policies\Base\RestaurantItemCrudPolicy;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MenuVersionChangePolicy.
 *
 * Changes of scheduled versions are managed by admins of their restaurant.
 */
class MenuVersionChangePolicy extends RestaurantItemCrudPolicy
{
    /**
     * Get the model of the policy.
     *
     * @return Model|string
     */
    public function model(): Model|string
    {
        return MenuVersionChange::class;
    }
}
