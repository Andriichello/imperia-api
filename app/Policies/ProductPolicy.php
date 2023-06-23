<?php

namespace App\Policies;

use App\Models\Morphs\Category;
use App\Models\Product;
use App\Models\User;
use App\Policies\Base\CrudPolicy;
use App\Policies\Base\RestaurantItemCrudPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;

/**
 * Class ProductPolicy.
 */
class ProductPolicy extends RestaurantItemCrudPolicy
{
    /**
     * Get the model of the policy.
     *
     * @return Model|string
     */
    public function model(): Model|string
    {
        return Product::class;
    }
}
