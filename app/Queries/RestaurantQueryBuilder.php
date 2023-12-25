<?php

namespace App\Queries;

use App\Models\User;

/**
 * Class RestaurantQueryBuilder.
 */
class RestaurantQueryBuilder extends BaseQueryBuilder
{
    /**
     * Apply index query conditions.
     *
     * @param User|null $user
     *
     * @return static
     */
    public function index(?User $user = null): static
    {
        $query = parent::index($user);

        if ($user->restaurant_id) {
            $query->where('id', $user->restaurant_id);
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
