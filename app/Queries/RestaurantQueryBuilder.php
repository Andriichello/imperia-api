<?php

namespace App\Queries;

use App\Models\Restaurant;
use App\Models\User;

/**
 * Class RestaurantQueryBuilder.
 *
 * @method Restaurant|null first($columns = ['*'])
 * @method Restaurant|null find($columns = ['*'])
 * @method $this where($column, $operator = null, $value = null, $boolean = 'and')
 * @method $this orWhere($column, $operator = null, $value = null)
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
     * Searches restaurants with given id or slug.
     *
     * @param string|null $idOrSlug
     *
     * @return static
     */
    public function search(string|null $idOrSlug): static
    {
        $this->where(is_numeric($idOrSlug) ? 'id' : 'slug', $idOrSlug);

        return $this;
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
