<?php

namespace App\Policies;

use App\Models\Morphs\Tag;
use App\Policies\Base\RestaurantItemCrudPolicy;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TagPolicy.
 */
class TagPolicy extends RestaurantItemCrudPolicy
{
    /**
     * Get the model of the policy.
     *
     * @return Model|string
     */
    public function model(): Model|string
    {
        return Tag::class;
    }
}
