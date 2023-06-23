<?php

namespace App\Queries;

use App\Enums\NotificationChannel;
use App\Models\User;

/**
 * Class RestaurantQueryBuilder.
 */
class RestaurantQueryBuilder extends BaseQueryBuilder
{
    /**
     * Apply index query conditions.
     *
     * @param User $user
     *
     * @return static
     */
    public function index(User $user): static
    {
        $query = parent::index($user);

        if (!empty($user->restaurants)) {
            $query->whereIn('id', $user->restaurants);
        }

        return $query;
    }

    /**
     * Only restaurants with given slugs.
     *
     * @param string ...$slugs
     *
     * @return static
     */
    public function withSlug(string ...$slugs): static
    {
        $this->whereIn('slug', $slugs);

        return $this;
    }

    /**
     * Only restaurants that are in given cities.
     *
     * @param string ...$cities
     *
     * @return static
     */
    public function inCities(string ...$cities): static
    {
        $this->whereIn('city', $cities);

        return $this;
    }

    /**
     * Only restaurants that are in given countries.
     *
     * @param string ...$countries
     *
     * @return static
     */
    public function inCountries(string ...$countries): static
    {
        $this->whereIn('country', $countries);

        return $this;
    }
}
