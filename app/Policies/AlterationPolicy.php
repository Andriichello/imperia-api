<?php

namespace App\Policies;

use App\Models\Morphs\Alteration;
use App\Policies\Base\RestaurantItemCrudPolicy;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AlterationPolicy.
 */
class AlterationPolicy extends RestaurantItemCrudPolicy
{
    /**
     * Get the model of the policy.
     *
     * @return Model|string
     */
    public function model(): Model|string
    {
        return Alteration::class;
    }
}
