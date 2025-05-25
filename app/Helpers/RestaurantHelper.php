<?php

namespace App\Helpers;

use App\Helpers\Interfaces\RestaurantHelperInterface;
use App\Models\Restaurant;

/**
 * Class RestaurantHelper.
 */
class RestaurantHelper implements RestaurantHelperInterface
{
    /**
     * Find a restaurant by given identifier (id or slug).
     *
     * @param string|int $idOrSlug
     *
     * @return Restaurant|null
     */
    public static function find(string|int $idOrSlug): ?Restaurant
    {
        return Restaurant::query()
            ->search($idOrSlug)
            ->first();
    }
}
