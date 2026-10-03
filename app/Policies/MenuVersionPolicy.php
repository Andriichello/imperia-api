<?php

namespace App\Policies;

use App\Models\MenuVersion;
use App\Policies\Base\RestaurantItemCrudPolicy;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MenuVersionPolicy.
 *
 * Scheduled versions are managed by admins of their restaurant.
 */
class MenuVersionPolicy extends RestaurantItemCrudPolicy
{
    /**
     * Get the model of the policy.
     *
     * @return Model|string
     */
    public function model(): Model|string
    {
        return MenuVersion::class;
    }
}
