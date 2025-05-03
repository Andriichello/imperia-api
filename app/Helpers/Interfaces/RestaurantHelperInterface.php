<?php

namespace App\Helpers\Interfaces;

use App\Models\Restaurant;

/**
 * Interface RestaurantHelperInterface.
 */
interface RestaurantHelperInterface
{
    /**
     * Find a restaurant by given identifier (id or slug).
     *
     * @param string|int $idOrSlug
     *
     * @return Restaurant|null
     */
    public static function find(string|int $idOrSlug): ?Restaurant;
}
