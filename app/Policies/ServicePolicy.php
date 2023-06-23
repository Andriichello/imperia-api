<?php

namespace App\Policies;

use App\Models\Morphs\Category;
use App\Models\Service;
use App\Models\User;
use App\Policies\Base\CrudPolicy;
use App\Policies\Base\RestaurantItemCrudPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;

/**
 * Class ServicePolicy.
 */
class ServicePolicy extends RestaurantItemCrudPolicy
{
    /**
     * Get the model of the policy.
     *
     * @return Model|string
     */
    public function model(): Model|string
    {
        return Service::class;
    }
}
