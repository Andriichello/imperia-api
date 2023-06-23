<?php

namespace App\Policies;

use App\Models\Morphs\Category;
use App\Models\Space;
use App\Models\User;
use App\Policies\Base\CrudPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;

/**
 * Class SpacePolicy.
 */
class SpacePolicy extends CrudPolicy
{
    /**
     * Get the model of the policy.
     *
     * @return Model|string
     */
    public function model(): Model|string
    {
        return Space::class;
    }
}
