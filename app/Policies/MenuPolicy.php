<?php

namespace App\Policies;

use App\Models\Menu;
use App\Policies\Base\RestaurantItemCrudPolicy;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MenuPolicy.
 */
class MenuPolicy extends RestaurantItemCrudPolicy
{
    /**
     * Get the model of the policy.
     *
     * @return Model|string
     */
    public function model(): Model|string
    {
        return Menu::class;
    }
}
