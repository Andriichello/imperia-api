<?php

namespace App\Queries;

use App\Models\User;
use App\Providers\MorphServiceProvider;

/**
 * Class TipQueryBuilder.
 */
class TipQueryBuilder extends BaseQueryBuilder
{
    /**
     * Apply index query conditions.
     *
     * @param User $user
     *
     * @return static
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function index(User $user): static
    {
        if ($user->isStaff()) {
            return $this;
        }

        $this->whereIn('in', []);

        return $this;
    }

    /**
     * Exclude tips for given classes.
     *
     * @param string ...$classes
     *
     * @return static
     */
    public function notForClasses(string ...$classes): static
    {
        $morphs = MorphServiceProvider::getMorphMap($classes);

        $this->whereNotIn('receiver_type', array_keys($morphs));

        return $this;
    }

    /**
     * Include only tips for given classes.
     *
     * @param string ...$classes
     *
     * @return static
     */
    public function forClasses(string ...$classes): static
    {
        $morphs = MorphServiceProvider::getMorphMap($classes);

        $this->whereIn('receiver_type', array_keys($morphs));

        return $this;
    }

    /**
     * Include only tips with given receiver ids.
     *
     * @param array $values
     *
     * @return static
     */
    public function whereReceiverId(array $values): static
    {
        $this->whereIn('receiver_id', $values);

        return $this;
    }

    /**
     * Only tips related to given restaurants.
     *
     * @param mixed ...$restaurants
     *
     * @return static
     */
    public function withRestaurant(mixed ...$restaurants): static
    {
        $ids = $this->extract('id', ...$restaurants);

        if (!empty($ids)) {
            $this->whereIn($this->model->getTable() . '.restaurant_id', $ids);
        }

        return $this;
    }
}
